<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function index()
    {
        $sites = Site::all();
        return view('sites.index', compact('sites'));
    }

    public function create()
    {
        return view('sites.create');
    }

    protected function validateSite(Request $request)
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url',
            'check_interval' => 'required|integer|min:1',
            'webhook_url' => 'nullable|url|max:255',
            'enable_logging' => 'boolean',
            'logs_per_page' => 'required_if:enable_logging,1|integer|min:1',
            'cooling_time' => 'required|integer|min:1',
            'max_retries' => ['required', 'integer', 'min:1', 'max:10'],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateSite($request);
        $validated['is_active'] = true; // Set default to active
        
        $site = Site::create($validated);

        return redirect()->route('sites.index')
            ->with('success', 'Site created successfully.');
    }

    public function edit(Site $site)
    {
        return view('sites.edit', compact('site'));
    }

    public function update(Request $request, Site $site)
    {
        $validated = $this->validateSite($request);
        $validated['is_active'] = $request->has('is_active');
        
        $site->update($validated);

        return redirect()->route('sites.index')
            ->with('success', 'Site updated successfully.');
    }

    public function show(Site $site)
    {
        // Check if logging is enabled for this site
        if (!$site->enable_logging) {
            return redirect()->route('sites.index')
                ->with('error', 'Logging is not enabled for this site.');
        }

        // Get logs with pagination
        $logs = $site->logs()
            ->orderBy('created_at', 'desc')
            ->paginate($site->logs_per_page);

        // Debug information
        // \Log::info('Site Logs Query:', [
        //     'site_id' => $site->id,
        //     'log_count' => $logs->count(),
        //     'per_page' => $site->logs_per_page
        // ]);

        return view('sites.show', compact('site', 'logs'));
    }

    public function destroy(Site $site)
    {
        $site->delete();
        return redirect()->route('sites.index')
            ->with('success', 'Site deleted successfully');
    }
} 