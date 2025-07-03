<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\LineItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'customer_id' => Customer::factory(),
            'currency' => $this->faker->currencyCode,
            'tax_rate' => $this->faker->randomFloat(2, 0, 20),
            'total_amount' => $this->faker->randomFloat(2, 100, 1000),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Invoice $invoice) {
            LineItem::factory()->count(rand(1, 5))->for($invoice)->create();
        });
    }
}
