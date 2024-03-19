<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AutorisationsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'AUT_directeur_section' => $this->faker->boolean(2),
            'AUT_securite_surface' => $this->faker->boolean(15),
            'AUT_pilote' => $this->faker->boolean(10),
            'AUT_secretaire' => $this->faker->boolean(20),
        ];
    }

    public function directeur() : Factory
    {
        return $this->state(function ($attributes) {
            return [
                'AUT_directeur_section' => true,
                'AUT_securite_surface' => false,
                'AUT_pilote' => false,
                'AUT_secretaire' => false,
            ];
        });
    }
    public function securite() : Factory
    {
        return $this->state(function ($attributes) {
            return [
                'AUT_directeur_section' => false,
                'AUT_securite_surface' => true,
                'AUT_pilote' => false,
                'AUT_secretaire' => false,
            ];
        });
    }
    public function pilote() : Factory
    {
        return $this->state(function ($attributes) {
            return [
                'AUT_directeur_section' => false,
                'AUT_securite_surface' => false,
                'AUT_pilote' => true,
                'AUT_secretaire' => false,
            ];
        });
    }
    public function secretaire() : Factory
    {
        return $this->state(function ($attributes) {
            return [
                'AUT_directeur_section' => false,
                'AUT_securite_surface' => false,
                'AUT_pilote' => false,
                'AUT_secretaire' => true,
            ];
        });
    }
}
