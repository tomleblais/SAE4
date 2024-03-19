<?php

namespace Database\Factories;

use App\Models\Adherent;
use App\Models\Lieu;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlongeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $min = $this->faker->numberBetween(2,4);
        $date = $this->faker->unique()->dateTimeBetween("-1 year", "+1 year");
        return [
            'PLO_lieu' => $this->faker->numberBetween(1,3),
            'PLO_bateau' => $this->faker->numberBetween(1,2),
            'PLO_date' => $date,
            'PLO_moment' => $this->faker->numberBetween(1,3),
            'PLO_min_plongeurs' => $min,
            'PLO_max_plongeurs' => $this->faker->numberBetween($min, 16),
            'PLO_niveau' => $this->faker->numberBetween(0,14),
            'PLO_active' => $this->faker->boolean(67),
            'PLO_etat' => 1,
            'PLO_pilote' => 3,
            'PLO_securite' => 2,
            'PLO_directeur' => $this->faker->randomElement(Adherent::where('ADH_niveau', 14)->get('ADH_id'))
        ];
    }
    public function minLevel($min=0) : Factory
    {
        return $this->state(function ($attributes) use ($min) {
            return [
                'PLO_niveau' => $this->faker->numberBetween($min,14),
            ];
        });
    }

}
