<?php

namespace Database\Factories;

use App\Enums\TransactionStatus;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;


    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'transaction_id' => (string) Str::uuid(),
            'email' => $this->faker->unique()->safeEmail(),
            'status' => TransactionStatus::PENDING->value,
            'amount' => $this->faker->randomFloat(2, 1, 100),
            'currency' => 'BTC',
        ];
    }
}
