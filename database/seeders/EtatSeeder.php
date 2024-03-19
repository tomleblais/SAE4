<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EtatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("INSERT INTO `PLO_ETATS` VALUES (1,'Créée'),(2,'Paramétrée'),(3,'Validée'),(4,'Dépassée'),(5,'Annulée');");
    }
}
