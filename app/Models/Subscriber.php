<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Subscriber extends Model
{
    protected $fillable = [
        'email', 'name', 'status', 'confirmation_token', 'source',
        'confirmed_at', 'unsubscribed_at', 'ip_address', 'user_agent',
    ];

    protected $casts = [
        'confirmed_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $m) {
            if (empty($m->confirmation_token)) {
                $m->confirmation_token = Str::random(64);
            }
        });
    }

    public function scopeConfirmed(Builder $q): Builder
    {
        return $q->where('status', 'confirmed');
    }

    public function scopePending(Builder $q): Builder
    {
        return $q->where('status', 'pending');
    }

    public function confirm(): void
    {
        $this->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);
    }

    public function unsubscribe(): void
    {
        $this->update([
            'status' => 'unsubscribed',
            'unsubscribed_at' => now(),
        ]);
    }
}
