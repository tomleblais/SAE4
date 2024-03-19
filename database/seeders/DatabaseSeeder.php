<?php

namespace Database\Seeders;

use App\Models\Autorisations;
use App\Models\Personne;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            NiveauSeeder::class,
            MomentSeeder::class,
            BateauSeeder::class,
            LieuSeeder::class,
            EtatSeeder::class,
        ]);
        Autorisations::factory()->directeur()->for(Personne::factory()->defaultDirector())->create();
    }

    public function test()
    {
        $this->call([
            TestPersonneSeeder::class
        ]);
    }
}
