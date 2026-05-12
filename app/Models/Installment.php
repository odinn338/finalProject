<?php

namespace App\Models;

use App\Models\Debt;
use App\Models\PaymentLog;
use Illuminate\Database\Eloquent\Model;

class Installment extends Model
{
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
        'recorded_by',
    ];

    protected $casts = [
        'amount'         => 'decimal:2',
        'paid_amount'    => 'decimal:2',
        'penalty_amount' => 'decimal:2',
        'due_date'       => 'date',
        'paid_date'      => 'date',
    ];

    public function debt()
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
            'pending' => 'لم يُسدد',
            'paid'    => 'مسدد',
            'overdue' => 'متأخر',
            'voided'  => 'ملغي',
            'partial' => 'جزئي',
            default   => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'warning',
            'paid'    => 'success',
            'overdue' => 'danger',
            'voided'  => 'secondary',
            'partial' => 'info',
            default   => 'secondary',
        };
    }

    public function getDaysOverdueAttribute(): int
    {
        if (!$this->isOverdue()) return 0;
        return $this->due_date->diffInDays(now());
    }
}
