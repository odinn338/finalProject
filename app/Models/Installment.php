<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Installment extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';

    const STATUS_PENDING_APPROVAL = 'pending_approval';   // ← NEW

    const STATUS_PAID = 'paid';

    const STATUS_OVERDUE = 'overdue';

    protected $fillable = [
        'debt_id',
        'user_id',
        'installment_number',
        'amount',
        'paid_amount',
        'penalty_amount',
        'due_date',
        'paid_date',
        'status',
        'notes',
        'payment_reference',
        'recorded_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'penalty_amount' => 'decimal:2',
        'due_date' => 'date',
        'paid_date' => 'date',
    ];

    public function debt(): BelongsTo
    {
        return $this->belongsTo(Debt::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function paymentLog()
    {
        return $this->hasMany(PaymentLog::class);
    }

    // ═══════════════════════════════
    // Helpers
    // ═══════════════════════════════

    public function getTotalDueAttribute(): float
    {
        return $this->amount + $this->penalty_amount;
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, $this->total_due - $this->paid_amount);
    }

    public function isOverdue(): bool
    {
        return $this->status === 'pending' && $this->due_date->isPast();
    }

    public function getStatusArabicAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'لم يُسدد',
            self::STATUS_PENDING_APPROVAL => 'بانتظار تأكيد الإدارة',
            self::STATUS_PAID => 'مسدد',
            self::STATUS_OVERDUE => 'متأخر',
            'voided' => 'ملغي',
            'partial' => 'مسدد جزئياً',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_PENDING_APPROVAL => 'info',
            self::STATUS_PAID => 'success',
            self::STATUS_OVERDUE => 'danger',
            'voided' => 'secondary',
            'partial' => 'info',
            default => 'secondary',
        };
    }

    public function getDaysOverdueAttribute(): int
    {
        if (! $this->isOverdue()) {
            return 0;
        }

        return $this->due_date->diffInDays(now());
    }

    public function isPendingApproval(): bool
    {
        return $this->status === self::STATUS_PENDING_APPROVAL;
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }
}
