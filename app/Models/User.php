<?php

namespace App\Models;

use App\Enums\UserRole;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'department', 'student_code', 'staff_code', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function createdBookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'created_by');
    }

    public function usageSessions(): HasMany
    {
        return $this->hasMany(UsageSession::class);
    }

    public function checkedInSessions(): HasMany
    {
        return $this->hasMany(UsageSession::class, 'checked_in_by');
    }

    public function completedSessions(): HasMany
    {
        return $this->hasMany(UsageSession::class, 'completed_by');
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isManager(): bool
    {
        return $this->role === UserRole::Manager;
    }

    public function isLabStaff(): bool
    {
        return $this->role === UserRole::LabStaff;
    }

    public function isResearcher(): bool
    {
        return $this->role === UserRole::Researcher;
    }

    public function canOperateLab(): bool
    {
        return $this->isAdmin() || $this->isLabStaff();
    }

    public function canViewManagementDashboard(): bool
    {
        return $this->isAdmin() || $this->isManager();
    }

    public function canManageSystem(): bool
    {
        return $this->isAdmin();
    }

    public function canUseBookingWorkspace(): bool
    {
        return $this->isAdmin() || $this->isLabStaff() || $this->isResearcher();
    }

    public function roleLabel(): string
    {
        return match ($this->role) {
            UserRole::Admin => 'Quản trị viên',
            UserRole::Manager => 'Quản lý phòng thí nghiệm',
            UserRole::LabStaff => 'Kỹ thuật viên',
            UserRole::Researcher => 'Người nghiên cứu / Sinh viên',
        };
    }
}
