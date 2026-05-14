<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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
        'credit_limit',
        'kyc_status',
        'kyc_verified_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'kyc_verified_at' => 'datetime',
        'password' => 'hashed',
        'credit_score' => 'decimal:2',
        'credit_limit' => 'decimal:2',
    ];

    // ═══════════════════════════════════════════════════
    //  العلاقات (Relationships) - محدثة لتطابق الكنترولرات
    // ═══════════════════════════════════════════════════

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    /** طلبات الديون المقدَّمة (للمدين) */
    public function debtRequests()
    {
        return $this->hasMany(DebtRequest::class);
    }

    /** الديون التي أعطاها كدائن (FIX: الربط بـ lender_id أو creditor_id حسب الكنترولر) */
    public function lentDebts()
    {
        // إذا كان كلود استخدم lender_id في الكنترولر، تأكد أنها مطابقة هنا
        return $this->hasMany(Debt::class, 'lender_id');
    }

    /** الديون التي اقترضها كمدين */
    public function borrowedDebts()
    {
        return $this->hasMany(Debt::class, 'debtor_id');
    }

    /** ديون المدين (اسم قصير للاستخدام في الواجهات) */
    public function debts()
    {
        return $this->hasMany(Debt::class, 'debtor_id');
    }

    public function installments()
    {
        return $this->hasMany(Installment::class);
    }

    public function walletTopups()
    {
        return $this->hasMany(WalletTopup::class);
    }

    public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    // ═══════════════════════════════════════════════════
    //  Role Helpers - فحص الأدوار (مطلوبة للـ Sidebar)
    // ═══════════════════════════════════════════════════

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isCreditor(): bool
    {
        return $this->role === 'creditor';
    }

    public function isDebtor(): bool
    {
        return $this->role === 'debtor';
    }

    public function hasRole($roles): bool
    {
        return in_array($this->role, (array) $roles);
    }

    // ═══════════════════════════════════════════════════
    //  Accessors - التسميات العربية والحسابات
    // ═══════════════════════════════════════════════════

    /** الرصيد المتاح (للعرض السريع في الـ Sidebar) */
    public function getWalletBalanceAttribute(): float
    {
        return (float) ($this->wallet?->available_balance ?? 0);
    }

    public function getRoleArabicAttribute(): string
    {
        return match ($this->role) {
            'admin' => 'مدير النظام',
            'creditor' => 'دائن (مُقرِض)',
            'debtor' => 'مدين (مُقترِض)',
            default => $this->role,
        };
    }

    public function getStatusArabicAttribute(): string
    {
        return $this->status === 'active' ? 'نشط' : 'موقوف';
    }

    // ═══════════════════════════════════════════════════
    //  Status Checks
    // ═══════════════════════════════════════════════════

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
