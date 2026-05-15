<?php

namespace Database\Factories;

use App\Models\Debt;
use App\Models\Installment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Installment>
 */
class InstallmentFactory extends Factory
{
    protected $model = Installment::class;

    public function definition(): array
    {
        return [
            'debt_id' => Debt::factory(),
            'user_id' => fn (array $attributes) => Debt::find($attributes['debt_id'])?->debtor_id
                ?? Debt::find($attributes['debt_id'])?->user_id,
            'installment_number' => 1,
            'amount' => 1000,
            'paid_amount' => 0,
            'penalty_amount' => 0,
            'due_date' => now()->addMonth()->toDateString(),
            'status' => Installment::STATUS_PENDING,
        ];
    }
}
