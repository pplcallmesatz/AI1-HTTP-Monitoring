<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Site extends Model
{
    protected $fillable = [
        'name',
        'url',
        'check_interval',
        'cooling_time',
        'webhook_url',
        'webhook_retry_count',
        'last_webhook_sent_at',
        'enable_logging',
        'logs_per_page',
        'is_active',
        'last_check_at',
        'is_down',
        'max_retries',
    ];

    protected $attributes = [
        'max_retries' => 3, // Default value
    ];

    protected $casts = [
        'last_check_at' => 'datetime',
        'last_webhook_sent_at' => 'datetime',
        'is_down' => 'boolean',
        'enable_logging' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected $withCount = ['logs'];

    public function logs()
    {
        return $this->hasMany(SiteLog::class);
    }

    public function getLastCheckedAttribute()
    {
        if (!$this->last_check_at) {
            return 'Never';
        }

        $lastCheck = Carbon::parse($this->last_check_at);
        return $lastCheck->diffForHumans() . ' (' . $lastCheck->format('Y-m-d H:i:s') . ')';
    }
} 