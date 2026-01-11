<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'department',
        'profile_photo_path',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'department'        => 'string',
            'password'          => 'hashed',
        ];
    }

    /**
     * Get the user's profile photo URL.
     *
     * @return string|null
     */
    public function getProfilePhotoUrlAttribute(): ?string
    {
        if (!$this->profile_photo_path) {
            return null;
        }

        return asset('storage/' . $this->profile_photo_path);
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->department === 'admin';
    }

    /**
     * Check if user belongs to specific department
     */
    public function isInDepartment(string $department): bool
    {
        return $this->department === $department;
    }

    public function getDashboardRoute()
    {
        return match (strtolower($this->department)) {
            'admin'         => '/dashboards/main',
            'sales'         => '/dashboards/sales',
            'inventory'     => '/dashboards/inventory',
            'logistics'     => '/dashboards/logistics',
            'production'    => '/dashboards/production',
            default         => '/dashboard',
        };
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }
}
