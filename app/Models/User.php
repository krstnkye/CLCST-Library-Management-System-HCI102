<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLES = ['student', 'faculty', 'librarian', 'admin', 'superadmin'];

    protected $fillable = [
        'patron_id', 'name', 'first_name', 'last_name', 'middle_initial', 'email',
        'program_or_department', 'mobile_number', 'credential_path', 'password',
    ];

    protected $hidden = ['password', 'remember_token', 'credential_path'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'approved_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function manageableRoles(): array
    {
        return match ($this->role) {
            'librarian' => ['student', 'faculty'],
            'admin' => ['student', 'faculty', 'librarian'],
            'superadmin' => self::ROLES,
            default => [],
        };
    }

    public function canManageRole(string $role): bool
    {
        return in_array($role, $this->manageableRoles(), true);
    }

    public function initials(): string
    {
        return collect(preg_split('/\s+/', trim($this->name)) ?: [])
            ->filter()
            ->take(2)
            ->map(fn (string $part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('') ?: 'U';
    }

    public function dashboardRouteName(): string
    {
        return match ($this->role) {
            'student' => 'student.dashboard',
            'faculty' => 'faculty.dashboard',
            'librarian' => 'librarian.dashboard',
            'admin' => 'admin.dashboard',
            'superadmin' => 'superadmin.dashboard',
            default => 'login',
        };
    }
}
