<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TipoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name, // ou ->unique()->word() se quiseres evitar repetições
        ];
    }
}
