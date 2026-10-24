<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ActiveSession extends DatabaseModel
{
    public $timestamps = false;

    protected $fillable = [
        'id',
        'session_id',
        'user_id',
        'user_email',
        'user_name',
        'user_role',
        'logged_in_at',
        'last_activity_at',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'logged_in_at' => 'datetime',
            'last_activity_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ActiveSession $session): void {
            $session->id ??= (string) Str::uuid();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
