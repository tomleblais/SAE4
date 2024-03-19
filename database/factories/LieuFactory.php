<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class LieuFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'LIE_libelle' => $this->faker->city(),
            'LIE_description' => $this->faker->realText(100),
            'LIE_active' => $this->faker->boolean(70),
        ];
    }

    public function active(): Factory {
        return $this->state(['LIE_active'=>true]);
    }
}
