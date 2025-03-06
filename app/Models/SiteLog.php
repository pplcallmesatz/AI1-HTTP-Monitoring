<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteLog extends Model
{
    public $timestamps = ['created_at']; // Only keep created_at timestamp
    const UPDATED_AT = null; // Disable updated_at

    protected $fillable = [
        'site_id',
        'status_code',
        'response_time',
        'status',
        'message',
        'webhook_sent',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'webhook_sent' => 'boolean',
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }
} 