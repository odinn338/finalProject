<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DebtRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'requested_amount',
        'requested_months',
        'approved_amount',
        'interest_rate',
        'approved_months',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
        'status',
    ];

    protected $casts = [
        'requested_amount' => 'decimal:2',
        'approved_amount'  => 'decimal:2',
        'interest_rate'    => 'decimal:2',
        'reviewed_at'      => 'datetime',
    ];

    // ═══════════════════════════════
    // العلاقات
    // ═══════════════════════════════

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function debt()
    {
        return $this->hasOne(Debt::class);
    }

    // ═══════════════════════════════
    // Helpers
    // ═══════════════════════════════

    public function getStatusArabicAttribute(): string
    {
        return match ($this->status) {
            'pending'   => 'قيد المراجعة',
            'approved'  => 'موافق عليه',
            'rejected'  => 'مرفوض',
            'cancelled' => 'ملغي',
            default     => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending'   => 'warning',
            'approved'  => 'success',
            'rejected'  => 'danger',
            'cancelled' => 'secondary',
            default     => 'secondary',
        };
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}
