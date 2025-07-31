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
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'locked_until',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'locked_until' => 'datetime',
    ];

    // Task relationship
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    // Account locking stuff
    public function isLocked(): bool
    {
        return $this->locked_until && $this->locked_until > now();
    }

    public function lockAccount(int $minutes = 30): void
    {
        $this->update(['locked_until' => now()->addMinutes($minutes)]);
    }

    public function unlockAccount(): void
    {
        $this->update(['locked_until' => null]);
    }
}
