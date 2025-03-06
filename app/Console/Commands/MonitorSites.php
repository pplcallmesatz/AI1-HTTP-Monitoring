<?php

namespace App\Console\Commands;

use App\Models\Site;
use App\Models\SiteLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class MonitorSites extends Command
{
    protected $signature = 'sites:monitor';
    protected $description = 'Monitor all registered sites';
    private $maxRetries = 3;

    public function handle()
    {
        $sites = Site::where('is_active', true)->get();

        foreach ($sites as $site) {
            try {
                $this->info("\n=== Site: {$site->name} ===");
                $this->info("Check Interval: {$site->check_interval} minutes");
                $this->info("Last Check: " . ($site->last_check_at ? $site->last_check_at->format('Y-m-d H:i:s') : 'Never'));

                // If never checked before, check it now
                if (!$site->last_check_at) {
                    $this->info("First time check");
                    $checkResult = $this->checkSite($site);
                    $this->processSiteStatus($site, $checkResult);
                    continue;
                }

                // Calculate minutes since last check
                $minutesSinceLastCheck = $site->last_check_at->diffInMinutes(now());
                $this->info("Minutes since last check: {$minutesSinceLastCheck}");

                // Check if enough time has passed
                if ($minutesSinceLastCheck >= $site->check_interval) {
                    $this->info("Time to check - Interval reached");
                    $checkResult = $this->checkSite($site);
                    $this->processSiteStatus($site, $checkResult);
                } else {
                    $minutesRemaining = $site->check_interval - $minutesSinceLastCheck;
                    $this->info("Skipping check - Next check in {$minutesRemaining} minutes");
                }
            } catch (\Exception $e) {
                // Log the error but continue with next site
                Log::error("Error processing site: {$site->name}", [
                    'error' => $e->getMessage(),
                    'site_id' => $site->id,
                    'site_name' => $site->name,
                    'trace' => $e->getTraceAsString()
                ]);
                
                $this->error("\nError processing site {$site->name}: " . $e->getMessage());
                
                // Continue with next site
                continue;
            }
        }
    }

    private function checkSite(Site $site): array
    {
        try {
            $this->info("\n=== Site Status Check: {$site->name} ===");
            $this->info("Previous status: " . ($site->is_down ? 'DOWN' : 'UP'));
            
            $startTime = microtime(true);
            $statusCode = null;
            $responseTime = 0;
            $isUp = false;

            // Configure HTTP client with increased max redirects
            $response = Http::withOptions([
                'allow_redirects' => [
                    'max'             => 10,
                    'strict'          => true,
                    'referer'         => true,
                    'protocols'       => ['http', 'https'],
                    'track_redirects' => true
                ],
                'timeout' => 30,
                'verify' => false  // Skip SSL verification if needed
            ])->get($site->url);

            $responseTime = round((microtime(true) - $startTime) * 1000, 2);
            $statusCode = $response->status();
            $isUp = $response->successful();
            
            // Get redirect count safely
            $stats = $response->handlerStats();
            $redirectCount = isset($stats['redirect_count']) ? $stats['redirect_count'] : 0;
            
            // Log successful response
            Log::info("Site Check Response", [
                'site_name' => $site->name,
                'url' => $site->url,
                'status' => $isUp ? 'UP' : 'DOWN',
                'status_code' => $statusCode,
                'response_time' => $responseTime . 'ms',
                'response_headers' => $response->headers(),
                'final_url' => $response->effectiveUri()->__toString(),
                'redirect_count' => $redirectCount,
                'timestamp' => now()->format('Y-m-d H:i:s'),
            ]);

            $this->info("Current status: " . ($isUp ? 'UP' : 'DOWN'));
            $this->info("Response time: {$responseTime}ms");
            $this->info("Status code: {$statusCode}");
            $this->info("Redirects: {$redirectCount}");

            return [
                'is_up' => $isUp,
                'status_code' => $statusCode,
                'response_time' => $responseTime
            ];

        } catch (\Exception $e) {
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);
            
            // Log error information
            Log::error("Site Check Failed", [
                'site_name' => $site->name,
                'url' => $site->url,
                'status' => 'DOWN',
                'error' => $e->getMessage(),
                'response_time' => $responseTime . 'ms',
                'timestamp' => now()->format('Y-m-d H:i:s'),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'is_up' => false,
                'status_code' => 503,
                'response_time' => $responseTime
            ];
        }
    }

    private function processSiteStatus(Site $site, array $checkResult): void
    {
        $isUp = $checkResult['is_up'];
        $statusCode = $checkResult['status_code'];
        $responseTime = $checkResult['response_time'];

        // If site is coming back up, reset webhook retry count
        if ($isUp && $site->is_down) {
            $site->update([
                'is_down' => false,
                'webhook_retry_count' => 0,
                'last_check_at' => now()
            ]);
        } else {
            $site->update([
                'is_down' => !$isUp,
                'last_check_at' => now()
            ]);
        }

        // Create log entry if logging is enabled
        if ($site->enable_logging) {
            SiteLog::create([
                'site_id' => $site->id,
                'status' => $isUp ? 'Active' : 'Inactive',
                'status_code' => $statusCode,
                'response_time' => $responseTime,
                'message' => $isUp ? 'Site is up' : 'Site is down',
                'webhook_sent' => false
            ]);
        }

        // Process webhook if site is down
        if (!$isUp && !empty($site->webhook_url)) {
            if ($this->shouldSendWebhook($site)) {
                if ($this->sendWebhook($site)) {
                    // Increment webhook retry count after successful send
                    $site->increment('webhook_retry_count');
                    
                    // Update the last webhook sent timestamp
                    $site->update(['last_webhook_sent_at' => now()]);
                    
                    // Update the log entry to mark webhook as sent
                    if ($site->enable_logging) {
                        SiteLog::where('site_id', $site->id)
                            ->latest()
                            ->first()
                            ->update(['webhook_sent' => true]);
                    }
                }
            }
        }
    }

    private function shouldSendWebhook(Site $site): bool
    {
        $maxRetries = $site->max_retries;
        $coolingPeriod = ($site->cooling_time ?? 2) * 60;

        // First time going down
        if (!$site->is_down) {
            Log::info("First time down - sending webhook");
            return true;
        }

        // Check max retries using site's webhook_retry_count
        if ($site->webhook_retry_count >= $maxRetries) {
            Log::info("Max retries reached - blocking webhook", [
                'current_retries' => $site->webhook_retry_count,
                'max_retries' => $maxRetries
            ]);
            return false;
        }

        // Check cooling period
        if ($site->last_webhook_sent_at) {
            $secondsSinceLastWebhook = now()->timestamp - $site->last_webhook_sent_at->timestamp;
            
            if ($secondsSinceLastWebhook >= $coolingPeriod) {
                Log::info("Cooling period passed - sending webhook");
                return true;
            }
            
            Log::info("Still in cooling period - blocking webhook");
            return false;
        }

        return true;
    }

    private function sendWebhook(Site $site)
    {
        $this->info("🚀 Attempting webhook delivery...");
        
        $payload = [
            'site_name' => $site->name,
            'site_url' => $site->url,
            'status' => $site->is_down ? 'Inactive' : 'Active',
            'message' => $site->is_down ? 'Site is down' : 'Site is up',
            'checked_at' => now()->toDateTimeString(),
        ];
        
        $this->info("📦 Payload: " . json_encode($payload, JSON_PRETTY_PRINT));
        $this->info("🌐 Sending to: " . $site->webhook_url);

        // Test webhook URL validity
        if (!filter_var($site->webhook_url, FILTER_VALIDATE_URL)) {
            throw new \Exception("Invalid webhook URL format");
        }

        $response = Http::timeout(30)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'User-Agent' => 'Site-Monitor/1.0'
            ])
            ->post($site->webhook_url, $payload);

        $this->info("📥 Response Status: " . $response->status());
        $this->info("📄 Response Headers: " . json_encode($response->headers(), JSON_PRETTY_PRINT));
        $this->info("📝 Response Body: " . $response->body());

        if ($response->successful()) {
            $site->last_webhook_sent_at = now();
            $site->save();
            
            $this->info("✅ Webhook delivered successfully!");
            return true;
        } else {
            $this->error("❌ Webhook delivery failed!");
            $this->error("Status: " . $response->status());
            $this->error("Body: " . $response->body());
            return false;
        }
    }
}