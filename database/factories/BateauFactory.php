<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BateauFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'BAT_libelle' => $this->faker->firstNameFemale(),
            'BAT_max_personnes' => $this->faker->numberBetween(4,20),
            'BAT_active' => $this->faker->boolean(70),
        ];
    }

    public function active(): Factory {
        return $this->state(['BAT_active'=>true]);
    }
}
