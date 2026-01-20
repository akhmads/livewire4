<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QueueLog extends Model
{
    protected $fillable = [
        'job_id',
        'job_name',
        'status',
        'started_at',
        'finished_at',
        'message',
        'data',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'data' => 'array',
    ];

    public function getDurationAttribute()
    {
        if ($this->started_at && $this->finished_at) {
            return $this->started_at->diffInSeconds($this->finished_at) . 's';
        }
        return null;
    }

    public function getProgressAttribute()
    {
        return match ($this->status) {
            'pending' => 0,
            'processing' => 50,
            'completed' => 100,
            'failed' => 0,
            default => 0,
        };
    }
}
