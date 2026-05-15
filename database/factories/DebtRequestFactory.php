<?php

namespace Database\Factories;

use App\Models\DebtRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DebtRequest>
 */
class DebtRequestFactory extends Factory
{
    protected $model = DebtRequest::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->debtor(),
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->paragraph(),
            'requested_amount' => fake()->randomFloat(2, 1000, 50000),
            'requested_months' => 12,
            'status' => 'approved',
            'approved_amount' => 10000,
            'interest_rate' => 10,
            'approved_months' => 12,
        ];
    }
}
