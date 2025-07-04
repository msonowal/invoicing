<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
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
            'name' => $this->faker->company,
            'address' => $this->faker->address,
            'gst_number' => $this->faker->regexify('[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}'),
            'pan_number' => $this->faker->regexify('[A-Z]{5}[0-9]{4}[A-Z]{1}'),
            'bank_name' => $this->faker->company,
            'account_number' => $this->faker->bankAccountNumber,
            'ifsc_code' => $this->faker->regexify('[A-Z]{4}0[0-9]{6}'),
        ];
    }
}
