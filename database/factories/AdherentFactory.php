<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AdherentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'ADH_licence' => $this->faker->unique()->numerify("################"),
            'ADH_date_certificat' => $this->faker->dateTimeBetween('-1 year'),
            'ADH_forfait' =>$this->faker->numberBetween(0, 200),
            'ADH_niveau' => $this->faker->numberBetween(0,14)
        ];
    }
}
