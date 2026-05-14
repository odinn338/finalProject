<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * نموذج المحفظة الرقمية
 *
 * @property int    $id
 * @property int    $user_id
 * @property float  $available_balance  الرصيد المتاح للاستخدام
 * @property float  $reserved_balance   الرصيد المحجوز (Escrow)
 * @property float  $total_deposited    إجمالي الإيداعات التراكمية
 * @property float  $total_withdrawn    إجمالي السحوبات التراكمية
 * @property string $status             active | frozen | suspended
 * @property string $currency           EGP | USD | SAR
 */
class Wallet extends Model
{
    protected $fillable = [
        'user_id',
        'available_balance',
        'reserved_balance',
        'total_deposited',
        'total_withdrawn',
        'status',
        'currency',
    ];

    protected $casts = [
        'available_balance' => 'decimal:2',
        'reserved_balance'  => 'decimal:2',
        'total_deposited'   => 'decimal:2',
        'total_withdrawn'   => 'decimal:2',
    ];

    // ═══════════════════════════════════════════════════
    //  العلاقات
    // ═══════════════════════════════════════════════════

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function topups()
    {
        return $this->hasMany(WalletTopup::class)->latest();
    }

    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class)->latest();
    }

    // ═══════════════════════════════════════════════════
    //  Computed Attributes
    // ═══════════════════════════════════════════════════

    /** الرصيد الكلي = متاح + محجوز */
    public function getTotalBalanceAttribute(): float
    {
        return (float) $this->available_balance + (float) $this->reserved_balance;
    }

    /** هل الرصيد كافٍ لمبلغ معين؟ */
    public function hasSufficientBalance(float $amount): bool
    {
        return (float) $this->available_balance >= $amount;
    }

    // ═══════════════════════════════════════════════════
    //  Status Helpers
    // ═══════════════════════════════════════════════════

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
    public function isFrozen(): bool
    {
        return $this->status === 'frozen';
    }

    public function getStatusArabicAttribute(): string
    {
        return match ($this->status) {
            'active'    => 'نشطة',
            'frozen'    => 'مجمّدة',
            'suspended' => 'موقوفة',
            default     => $this->status,
        };
    }
}
