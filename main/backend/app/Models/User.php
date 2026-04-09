<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_blocked',
        'is_admin',
        'is_owner',
        'last_login_at',
        'last_logout_at',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_blocked' => 'boolean',
            'is_admin' => 'boolean',
            'is_owner' => 'boolean',
            'last_login_at' => 'datetime',
            'last_logout_at' => 'datetime',
        ];
    }

    public function applications(): HasMany
    {
        return $this->hasMany(AdoptionApplication::class, 'user_id');
    }

    public function contactMessages(): HasMany
    {
        return $this->hasMany(ContactMessage::class, 'user_id');
    }

    public function supportChatMessages(): HasMany
    {
        return $this->hasMany(SupportChatMessage::class, 'user_id');
    }
}
