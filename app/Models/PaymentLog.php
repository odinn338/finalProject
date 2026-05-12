<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentLog extends Model
{
    protected $fillable = [
        'installment_id',
        'debt_id',
        'user_id',
        'recorded_by',
        'amount_paid',
        'payment_method',
        'reference_number',
        'notes',
        'payment_date',
    ];

    protected $casts = [
        'amount_paid'  => 'decimal:2',
        'payment_date' => 'datetime',
    ];

    public function installment()
    {
        return $this->belongsTo(Installment::class);
    }

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

    public function getPaymentMethodArabicAttribute(): string
    {
        return match ($this->payment_method) {
            'cash'          => 'نقداً',
            'bank_transfer' => 'تحويل بنكي',
            'cheque'        => 'شيك',
            default         => $this->payment_method,
        };
    }
}
