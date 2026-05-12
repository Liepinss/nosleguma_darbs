<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportChatMessage extends Model
{
    protected $fillable = [
        'user_id',
        'is_from_admin',
        'admin_user_id',
        'body',
        'read_by_user_at',
        'read_by_admin_at',
    ];

    protected function casts(): array
    {
        return [
            'is_from_admin' => 'boolean',
            'read_by_user_at' => 'datetime',
            'read_by_admin_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function adminUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }
}
