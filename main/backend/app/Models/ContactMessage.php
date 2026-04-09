<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactMessage extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'email',
        'message',
        'selected_animals',
        'source',
        'status',
        'is_read',
        'sent_at',
        'moderated_at',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'sent_at' => 'datetime',
            'moderated_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
