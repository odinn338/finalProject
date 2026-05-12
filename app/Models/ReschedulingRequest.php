<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReschedulingRequest extends Model
{
    protected $fillable = [
        'debt_id',
        'user_id',
        'reason',
        'outstanding_balance',
        'remaining_installments',
        'new_interest_rate',
        'new_months',
        'new_monthly_installment',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
        'status',
    ];

    protected $casts = [
        'outstanding_balance'    => 'decimal:2',
        'new_interest_rate'      => 'decimal:2',
        'new_monthly_installment' => 'decimal:2',
        'reviewed_at'            => 'datetime',
    ];

    public function debt()
    {
        return $this->belongsTo(Debt::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function getStatusArabicAttribute(): string
    {
        return match ($this->status) {
            'pending'  => 'قيد المراجعة',
            'approved' => 'موافق عليه',
            'rejected' => 'مرفوض',
            default    => $this->status,
        };
    }
}
