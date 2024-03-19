<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BateauSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("INSERT INTO `PLO_BATEAUX` VALUES
                              (1,'Karaboudjan',10,1),
                              (2,'Esperanza',42,0),
                              (3,'La Licorne',25,1)
                              ;");
    }
}
