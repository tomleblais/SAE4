<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LieuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("INSERT INTO `PLO_LIEUX` VALUES
                            (1,'Baz Lucs','Plateau rocheux 0-30m',1),
                            (2,'Île Verte','Gros éboulis rocheux 0-20m',1),
                            (3,'Corbeau','Tombant 0-30m',1),
                            (4,'Ifs','Plongée dans les égouts 0-6m',0);");
    }
}
