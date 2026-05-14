<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * نموذج حركة المحفظة — سجل القيد المزدوج
 * كل تغيير في أي رصيد يُسجَّل هنا بدقة كاملة
 */
class WalletTransaction extends Model
{
    protected $fillable = [
        'wallet_id',
        'user_id',
        'counterpart_wallet_id',
        'counterpart_user_id',
        'type',
        'amount',
        'currency',
        'balance_before',
        'balance_after',
        'reserved_before',
        'reserved_after',
        'referenceable_type',
        'referenceable_id',
        'description',
        'reference_code',
        'admin_notes',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'reserved_before' => 'decimal:2',
        'reserved_after' => 'decimal:2',
    ];

    // ═══════════════════════════════════════════════════
    //  العلاقات
    // ═══════════════════════════════════════════════════

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function counterpartWallet()
    {
        return $this->belongsTo(Wallet::class, 'counterpart_wallet_id');
    }

    public function counterpartUser()
    {
        return $this->belongsTo(User::class, 'counterpart_user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** الكيان المرتبط (Polymorphic): Installment أو WalletTopup */
    public function referenceable()
    {
        return $this->morphTo();
    }

    // ═══════════════════════════════════════════════════
    //  Type Helpers
    // ═══════════════════════════════════════════════════

    public function getTypeArabicAttribute(): string
    {
        return match ($this->type) {
            'deposit' => 'إيداع',
            'withdrawal' => 'سحب',
            'payment_debit' => 'دفع قسط (خصم)',
            'payment_credit' => 'استلام قسط (إضافة)',
            'reserve' => 'حجز رصيد',
            'release' => 'تحرير حجز',
            'refund' => 'استرداد',
            'fee' => 'رسوم منصة',
            'adjustment' => 'تسوية يدوية',
            default => $this->type,
        };
    }

    /** هل هذه حركة إضافة أم خصم؟ */
    public function isCredit(): bool
    {
        return in_array($this->type, ['deposit', 'payment_credit', 'release', 'refund']);
    }

    public function getDirectionColorAttribute(): string
    {
        return $this->isCredit() ? 'success' : 'danger';
    }

    public function getDirectionSymbolAttribute(): string
    {
        return $this->isCredit() ? '+' : '-';
    }
}
