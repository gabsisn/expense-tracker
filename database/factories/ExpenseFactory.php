<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Expense>
 */
class ExpenseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'spent_on' => fake()->dateTimeBetween('-8 weeks')->format('Y-m-d'),
            'amount' => fake()->randomFloat(2, 2, 250),
            'description' => fake()->randomElement(['Coffee', 'Groceries', 'Train ticket', 'Lunch', 'Books', 'Fuel', 'Cloud bill']),
        ];
    }
}
