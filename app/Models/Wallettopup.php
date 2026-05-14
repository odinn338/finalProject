<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * نموذج طلب شحن المحفظة
 * يدعم مسارَي الدفع: Gateway الإلكتروني + التحويل اليدوي بإيصال
 */
class WalletTopup extends Model
{
    protected $fillable = [
        'wallet_id',
        'user_id',
        'amount',
        'currency',
        'payment_method',
        'gateway_transaction_id',
        'gateway_provider',
        'gateway_response',
        'gateway_order_id',
        'paymob_token',
        'receipt_image_path',
        'receipt_image_original_name',
        'transfer_reference',
        'user_notes',
        'reviewed_by',
        'reviewed_at',
        'admin_notes',
        'status',
    ];

    protected $casts = [
        'amount'           => 'decimal:2',
        'gateway_response' => 'array',
        'reviewed_at'      => 'datetime',
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
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // ═══════════════════════════════════════════════════
    //  Status Helpers
    // ═══════════════════════════════════════════════════

    public function isPending(): bool
    {
        return $this->status === 'pending_review';
    }
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function getStatusArabicAttribute(): string
    {
        return match ($this->status) {
            'pending_review'  => 'قيد المراجعة',
            'pending_gateway' => 'ينتظر البوابة',
            'completed'       => 'مكتمل',
            'rejected'        => 'مرفوض',
            'cancelled'       => 'ملغي',
            default           => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending_review', 'pending_gateway' => 'warning',
            'completed'  => 'success',
            'rejected'   => 'danger',
            'cancelled'  => 'secondary',
            default      => 'secondary',
        };
    }

    public function getPaymentMethodArabicAttribute(): string
    {
        return match ($this->payment_method) {
            'gateway'       => 'بوابة إلكترونية',
            'vodafone_cash' => 'فودافون كاش',
            'bank_transfer' => 'تحويل بنكي',
            'cash_deposit'  => 'إيداع نقدي',
            'cheque'        => 'شيك بنكي',
            default         => $this->payment_method
        };
    }

    // ═══════════════════════════════════════════════════
    //  Receipt Helpers
    // ═══════════════════════════════════════════════════

    public function hasReceipt(): bool
    {
        return !empty($this->receipt_image_path);
    }

    /** رابط مؤقت آمن (30 دقيقة) للإيصال المحفوظ في private disk */
    public function getReceiptTemporaryUrl(): ?string
    {
        if (!$this->hasReceipt()) return null;

        try {
            return Storage::disk('private')->temporaryUrl(
                $this->receipt_image_path,
                now()->addMinutes(30)
            );
        } catch (\Exception) {
            return null;
        }
    }
}
