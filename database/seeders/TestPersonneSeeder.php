<?php

namespace Database\Seeders;

use App\Models\Adherent;
use App\Models\Autorisations;
use App\Models\Personne;
use Illuminate\Database\Seeder;
use Illuminate\Support\HigherOrderCollectionProxy;

class TestPersonneSeeder extends Seeder
{
    public static int $director = 1;
    public static int $pilot = 2;
    public static int $security = 3;
    public static int $secretary = 4;
    /**
     * @var HigherOrderCollectionProxy|mixed
     */
    public static $diveDirector;

    /**
     * Run the database seeds.
     * @param int $nb the number of adherents to seed or 50
     * @return void
     */
    public static function run(int $nb = 50)
    {
        // One of each authorization
        $personnes = Personne::factory()->active()->count(4)->create();
        Autorisations::factory()->for($personnes[0])->directeur()->create();
        self::$director = $personnes[0]->PER_id;
        Autorisations::factory()->for($personnes[1])->securite()->create();
        self::$security = $personnes[1]->PER_id;
        Autorisations::factory()->for($personnes[2])->pilote()->create();
        self::$pilot = $personnes[2]->PER_id;
        Autorisations::factory()->for($personnes[3])->secretaire()->create();
        self::$secretary = $personnes[3]->PER_id;
        // At least one directeur de plongee
        self::$diveDirector = Personne::factory()->active()->has(Adherent::factory()->state(['ADH_niveau' => 14]))->create()
            ->PER_id;
        // Create $nb adherents
        Personne::factory()->count($nb)->has(Adherent::factory())->create();
    }

}
