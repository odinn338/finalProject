<?php

namespace Database\Factories;

use App\Models\Debt;
use App\Models\DebtRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Debt>
 */
class DebtFactory extends Factory
{
    protected $model = Debt::class;

    public function definition(): array
    {
        $principal = fake()->randomFloat(2, 1000, 50000);
        $interest = round($principal * 0.1, 2);
        $total = $principal + $interest;
        $months = 12;
        $monthly = round($total / $months, 2);

        return [
            'debt_request_id' => DebtRequest::factory(),
            'user_id' => fn (array $attributes) => DebtRequest::find($attributes['debt_request_id'])?->user_id
                ?? User::factory()->debtor()->create()->id,
            'debtor_id' => null,
            'lender_id' => null,
            'reference_number' => 'DM-'.fake()->unique()->numerify('####'),
            'principal_amount' => $principal,
            'interest_rate' => 10,
            'interest_amount' => $interest,
            'total_amount' => $total,
            'monthly_installment' => $monthly,
            'total_paid' => 0,
            'remaining_balance' => $total,
            'total_months' => $months,
            'paid_months' => 0,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths($months)->toDateString(),
            'status' => 'active',
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Debt $debt): void {
            if ($debt->debtor_id === null && $debt->user_id) {
                $debt->debtor_id = $debt->user_id;
            }
        })->afterCreating(function (Debt $debt): void {
            if ($debt->debtor_id === null && $debt->user_id) {
                $debt->update(['debtor_id' => $debt->user_id]);
            }
        });
    }

    public function withParties(User $debtor, User $creditor): static
    {
        return $this->state(fn () => [
            'user_id' => $debtor->id,
            'debtor_id' => $debtor->id,
            'lender_id' => $creditor->id,
        ]);
    }
}
