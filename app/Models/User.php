<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'national_id',
        'phone',
        'address',
        'password',
        'role',
        'status',
        'credit_score',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'credit_score'      => 'decimal:2',
    ];

    // ═══════════════════════════════
    // العلاقات
    // ═══════════════════════════════

    public function debtRequests()
    {
        return $this->hasMany(DebtRequest::class);
    }

    public function debts()
    {
        return $this->hasMany(Debt::class);
    }

    public function installments()
    {
        return $this->hasMany(Installment::class);
    }

    public function reschedulingRequests()
    {
        return $this->hasMany(ReschedulingRequest::class);
    }

    public function paymentLogs()
    {
        return $this->hasMany(PaymentLog::class);
    }

    // ═══════════════════════════════
    // Helpers
    // ═══════════════════════════════

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function getTotalDebtAttribute(): float
    {
        return $this->debts()->where('status', 'active')->sum('remaining_balance');
    }

    public function getRoleArabicAttribute(): string
    {
        return match ($this->role) {
            'admin' => 'مدير النظام',
            'user'  => 'مستخدم',
            default => $this->role,
        };
    }
}
