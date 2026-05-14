<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * نموذج المستخدم — يدعم ثلاثة أدوار: مدير · دائن · مدين
 *
 * @property int    $id
 * @property string $name
 * @property string $email
 * @property string $role          admin | creditor | debtor
 * @property string $status        active | suspended
 * @property string $kyc_status    not_submitted | pending | verified | rejected
 * @property float  $credit_score  درجة الائتمان 0-100
 * @property float  $credit_limit  الحد الائتماني الأقصى
 */
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
        'kyc_verified_at'   => 'datetime',
        'password'          => 'hashed',
        'credit_score'      => 'decimal:2',
        'credit_limit'      => 'decimal:2',
    ];

    // ═══════════════════════════════════════════════════
    //  العلاقات (Relationships)
    // ═══════════════════════════════════════════════════

    /** المحفظة الرقمية للمستخدم (واحد-لواحد) */
    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    /** طلبات الديون المقدَّمة (للمدين) */
    public function debtRequests()
    {
        return $this->hasMany(DebtRequest::class);
    }

    /** الديون المرتبطة بهذا المستخدم */
    public function debts()
    {
        return $this->hasMany(Debt::class);
    }

    /** الديون التي أعطاها كدائن */
    public function lentDebts()
    {
        return $this->hasMany(Debt::class, 'creditor_id');
    }

    /** الأقساط */
    public function installments()
    {
        return $this->hasMany(Installment::class);
    }

    /** طلبات شحن المحفظة */
    public function walletTopups()
    {
        return $this->hasMany(WalletTopup::class);
    }

    /** حركات المحفظة */
    public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    // ═══════════════════════════════════════════════════
    //  Role Helpers — فحص الأدوار
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

    public function hasRole(string|array $roles): bool
    {
        return in_array($this->role, (array) $roles);
    }

    // ═══════════════════════════════════════════════════
    //  Wallet Helpers — مساعدات المحفظة
    // ═══════════════════════════════════════════════════

    /** الرصيد المتاح في المحفظة */
    public function getWalletBalanceAttribute(): float
    {
        return (float) ($this->wallet?->available_balance ?? 0);
    }

    /** الرصيد الكلي (متاح + محجوز) */
    public function getWalletTotalAttribute(): float
    {
        $w = $this->wallet;
        return (float) (($w?->available_balance ?? 0) + ($w?->reserved_balance ?? 0));
    }

    // ═══════════════════════════════════════════════════
    //  Arabic Labels — التسميات العربية
    // ═══════════════════════════════════════════════════

    public function getRoleArabicAttribute(): string
    {
        return match ($this->role) {
            'admin'    => 'مدير النظام',
            'creditor' => 'دائن (مُقرِض)',
            'debtor'   => 'مدين (مُقترِض)',
            default    => $this->role,
        };
    }

    public function getRoleBadgeColorAttribute(): string
    {
        return match ($this->role) {
            'admin'    => 'danger',
            'creditor' => 'success',
            'debtor'   => 'warning',
            default    => 'secondary',
        };
    }

    public function getStatusArabicAttribute(): string
    {
        return $this->status === 'active' ? 'نشط' : 'موقوف';
    }

    public function getKycStatusArabicAttribute(): string
    {
        return match ($this->kyc_status) {
            'not_submitted' => 'لم يُقدَّم',
            'pending'       => 'قيد المراجعة',
            'verified'      => 'موثَّق',
            'rejected'      => 'مرفوض',
            default         => $this->kyc_status,
        };
    }

    // ═══════════════════════════════════════════════════
    //  Status Checks
    // ═══════════════════════════════════════════════════

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
    public function isKycVerified(): bool
    {
        return $this->kyc_status === 'verified';
    }
}
