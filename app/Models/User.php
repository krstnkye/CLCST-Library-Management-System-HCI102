<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLES = ['student', 'faculty', 'librarian', 'admin', 'superadmin'];

    protected $fillable = [
        'patron_id', 'name', 'first_name', 'last_name', 'middle_initial', 'email',
        'role', 'status', 'program_or_department', 'mobile_number',
        'credential_path', 'approved_at', 'approved_by', 'last_login_at', 'password',
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

    public const PATRON_ROLES = ['student', 'faculty'];

    public const STAFF_ROLES = ['librarian', 'admin', 'superadmin'];

    /** Roles that may apply through the public registration form. */
    public const SELF_REGISTER_ROLES = ['student', 'faculty', 'librarian'];

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function fines(): HasMany
    {
        return $this->hasMany(Fine::class);
    }

    public function libraryRequests(): HasMany
    {
        return $this->hasMany(LibraryRequest::class);
    }

    public static function isInstitutionalEmail(string $email): bool
    {
        return str_ends_with(mb_strtolower(trim($email)), '@'.config('library.allowed_email_domain'));
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isPatron(): bool
    {
        return in_array($this->role, self::PATRON_ROLES, true);
    }

    public function isStaff(): bool
    {
        return in_array($this->role, self::STAFF_ROLES, true);
    }

    public function roleLabel(): string
    {
        return match ($this->role) {
            'admin' => 'Administrator',
            'superadmin' => 'Super Administrator',
            default => ucfirst($this->role),
        };
    }

    public function outstandingFineTotal(): float
    {
        return (float) $this->fines()->where('status', 'outstanding')->sum('amount');
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
