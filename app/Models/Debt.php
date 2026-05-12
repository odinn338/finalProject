<?php

namespace App\Models;

use App\Models\Installment;
use App\Models\PaymentLog;
use App\Models\ReschedulingRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Debt extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'debt_request_id',
        'reference_number',
        'principal_amount',
        'interest_rate',
        'interest_amount',
        'total_amount',
        'monthly_installment',
        'total_paid',
        'remaining_balance',
        'total_months',
        'paid_months',
        'start_date',
        'end_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'principal_amount'    => 'decimal:2',
        'interest_rate'       => 'decimal:2',
        'interest_amount'     => 'decimal:2',
        'total_amount'        => 'decimal:2',
        'monthly_installment' => 'decimal:2',
        'total_paid'          => 'decimal:2',
        'remaining_balance'   => 'decimal:2',
        'start_date'          => 'date',
        'end_date'            => 'date',
    ];

    // ═══════════════════════════════
    // العلاقات
    // ═══════════════════════════════

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function debtRequest()
    {
        return $this->belongsTo(DebtRequest::class);
    }

    public function installments()
    {
        return $this->hasMany(Installment::class)->orderBy('installment_number');
    }

    public function pendingInstallments()
    {
        return $this->hasMany(Installment::class)->whereIn('status', ['pending', 'overdue'])->orderBy('due_date');
    }

    public function overdueInstallments()
    {
        return $this->hasMany(Installment::class)->where('status', 'overdue')->orderBy('due_date');
    }

    public function reschedulingRequests()
    {
        return $this->hasMany(ReschedulingRequest::class);
    }

    public function paymentLogs()
    {
        return $this->hasMany(PaymentLog::class)->latest('payment_date');
    }

    // ═══════════════════════════════
    // Computed Attributes
    // ═══════════════════════════════

    public function getProgressPercentageAttribute(): float
    {
        if ($this->total_amount <= 0) return 0;
        return round(($this->total_paid / $this->total_amount) * 100, 1);
    }

    public function getStatusArabicAttribute(): string
    {
        return match ($this->status) {
            'active'      => 'نشط',
            'completed'   => 'مكتمل',
            'overdue'     => 'متأخر',
            'rescheduled' => 'معاد جدولته',
            default       => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'active'      => 'primary',
            'completed'   => 'success',
            'overdue'     => 'danger',
            'rescheduled' => 'warning',
            default       => 'secondary',
        };
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * هل يمكن تقديم طلب إعادة جدولة؟
     * الشرط: الدين نشط وليس هناك طلب جدولة قيد الانتظار
     */
    public function canBeRescheduled(): bool
    {
        if (!$this->isActive()) return false;
        return !$this->reschedulingRequests()->where('status', 'pending')->exists();
    }
}
