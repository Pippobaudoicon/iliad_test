<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    public function definition()
    {
        return [
            'customer_name' => $this->faker->name(),
            'description' => $this->faker->sentence()
        ];
    }
}
