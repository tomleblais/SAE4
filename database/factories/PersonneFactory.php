<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class PersonneFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $password = $this->faker->password(8).'aZ-1'; // ensure at least one of each category
        return [
            'PER_nom' => $this->faker->unique()->lastName(),
            'PER_prenom' => $this->faker->firstName(),
            'PER_pass' => Hash::make($password),
            'PER_email' => $this->faker->unique()->email(),
            'PER_remember_token' => $password,
            'PER_active' => $this->faker->boolean(70),
        ];
    }

    public function active(): Factory {
        return $this->state(['PER_active'=>true]);
    }

    public function defaultDirector(): Factory
    {
        return $this->state([
            'PER_nom' => "Dupont",
            'PER_prenom' => "Dupond",
            'PER_pass' => Hash::make("DupDup-14"),
            'PER_email' => "dupondt@iut.fr",
            'PER_remember_token' => null,
            'PER_active' => true,
        ]);
    }
}
