<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NiveauSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("INSERT INTO `PLO_NIVEAUX` VALUES
                              (0,'PB','Plongeur Bronze',6,0,0,0,0),
                              (1,'PA','Plongeur Argent',6,0,1,0,0),
                              (2,'PO-12','Plongeur OR -12ans',12,0,2,0,0),
                              (3,'PO+12','Plongeur OR +12ans',20,0,3,0,0),
                              (4,'PE-6','Plongeur Encadré 6m',6,0,10,0,0),
                              (5,'PE-12','Plongeur Encadré 12m',12,0,11,0,0),
                              (6,'PE-20','Plongeur Encadré 20m',20,0,12,0,0),
                              (7,'PE-40','Plongeur Encadré 40m',40,20,21,0,0),
                              (8,'PA-20','Plongeur Autonome 20m',40,20,20,0,0),
                              (9,'PA-40','Plongeur Autonome 40m',0,40,30,0,0),
                              (10,'PA-60','Plongeur Autonome 60m',0,60,31,0,0),
                              (11,'E1','Encadrant Niveau 1',0,60,32,0,0),
                              (12,'E2','Encadrant Niveau 2',0,60,40,1,0),
                              (13,'E3','Encadrant Niveau 3',0,60,50,1,0),
                              (14,'E4','Encadrant Niveau 4',0,60,51,1,1);");
    }
}
