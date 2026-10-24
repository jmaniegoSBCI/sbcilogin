<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ActivityLog extends DatabaseModel
{
    public const EVENT_LOGIN = 'login';

    public const EVENT_LOGOUT = 'logout';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'user_email',
        'user_name',
        'user_role',
        'event',
        'occurred_at',
        'ip_address',
    ];

    protected function casts(): array
    {
        return ['occurred_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::creating(function (ActivityLog $log): void {
            $log->id ??= (string) Str::uuid();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
