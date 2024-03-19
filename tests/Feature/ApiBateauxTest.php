<?php

namespace Tests\Feature;

use App\Models\Bateau;
use App\Models\Plongee;
use Database\Seeders\TestPersonneSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ApiBateauxTest extends TestCase
{
    use RefreshDatabase;

    public function test_getBateaux()
    {
        $response = $this->getJson('/api/bateaux');
        $response->assertStatus(200);
        $response->assertExactJson( [
            ["id"=>1,"libelle"=>"Karaboudjan", "max_personnes"=>10, "actif"=>true],
            ["id"=>3,"libelle"=>"La Licorne", "max_personnes"=>25, "actif"=>true],
        ]);
    }
    public function test_getBateauxInactifs()
    {
        $response = $this->getJson('/api/bateaux/inactifs');
        $response->assertStatus(200);
        $response->assertExactJson( [
            ["id"=>2,"libelle"=>"Esperanza", "max_personnes"=>42, "actif"=>false]
        ]);
    }
    public function test_getOneBateauOk()
    {
        DB::beginTransaction();
        try {
            /** @var Bateau $ship */
            $ship = Bateau::factory()->create();
            $response = $this->getJson("/api/bateaux/$ship->BAT_id");
            $response->assertStatus(200);
            $response->assertExactJson(
                ["id" => $ship->BAT_id, "libelle" => $ship->BAT_libelle, "max_personnes" => $ship->BAT_max_personnes, "actif" => $ship->BAT_active]
            );
        } finally {
            DB::rollBack();
        }
    }
    public function test_getOneBateauFail()
    {
        $response = $this->getJson('/api/bateaux/0');
        $response->assertStatus(404);
    }
    public function test_postOneBateauOk()
    {
        DB::beginTransaction();
        try {
            $response = $this->postJson("/api/bateaux", ['libelle'=>'test', 'max_personnes'=>17]);
            $response->assertStatus(200);
            $response->assertJsonFragment(
                ["libelle" => 'test', "max_personnes" => 17, "actif" => true]
            );
            $id=$response->json('id');
            self::assertNotNull($id);
            $ship = Bateau::find($id);
            self::assertNotNull($ship);
            self::assertEquals('test', $ship->BAT_libelle);
        } finally {
            DB::rollBack();
        }
    }
    public function test_putBateauOk() {
        DB::beginTransaction();
        try {
            /** @var Bateau $ship */
            $ship = Bateau::factory()->active()->create();
            $response = $this->putJson("/api/bateaux/$ship->BAT_id", ['libelle'=>'test', 'max_personnes'=>17]);
            $response->assertStatus(200);
            $response->assertJsonFragment(
                ["libelle" => 'test', "max_personnes" => 17, "actif" => true]
            );
            $ship->refresh();
            self::assertEquals('test', $ship->BAT_libelle);
            self::assertEquals(17, $ship->BAT_max_personnes);
        } finally {
            DB::rollBack();
        }
    }
    public function test_putBateauFail() {
        DB::beginTransaction();
        try {
            /** @var Bateau $ship */
            $ship = Bateau::factory()->active()->create();
            $response = $this->putJson("/api/bateaux/$ship->BAT_id", [
                'libelle'=>'testtesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttest', // too long
                'max_personnes'=>-17]); // not >=2
            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['libelle','max_personnes']);
        } finally {
            DB::rollBack();
        }
    }
    public function test_putBateauOkWithId() {
        DB::beginTransaction();
        try {
            /** @var Bateau $ship */
            $ship = Bateau::factory()->active()->create();
            $response = $this->putJson("/api/bateaux", ['id'=>$ship->BAT_id,'libelle'=>'test', 'max_personnes'=>17]);
            $response->assertStatus(200);
            $response->assertJsonFragment(
                ["libelle" => 'test', "max_personnes" => 17, "actif" => true]
            );
            $ship->refresh();
            self::assertEquals('test', $ship->BAT_libelle);
            self::assertEquals(17, $ship->BAT_max_personnes);
        } finally {
            DB::rollBack();
        }
    }
    public function test_putBateauFailWithId() {
        DB::beginTransaction();
        try {
            /** @var Bateau $ship */
            $ship = Bateau::factory()->active()->create();
            $response = $this->putJson("/api/bateaux", [
                'id' => $ship->BAT_id,
                'libelle'=>'testtesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttest', // too long
                'max_personnes'=>1]); // not >=2
            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['libelle','max_personnes']);
        } finally {
            DB::rollBack();
        }
    }
    public function test_deleteOk() {
        DB::beginTransaction();
        try {
            /** @var Bateau $ship */
            $ship = Bateau::factory()->active()->create();
            $response = $this->deleteJson("/api/bateaux/$ship->BAT_id");
            $response->assertStatus(200);

            self::assertNull( Bateau::find($ship->BAT_id) );
        } finally {
            DB::rollBack();
        }
    }
    public function test_deleteInactive() {
        DB::beginTransaction();
        try {
            /** @var Bateau $ship */
            $ship = Bateau::factory()->active()->create();
            TestPersonneSeeder::run(0);
            Plongee::factory()->state([
                'PLO_bateau' => $ship->BAT_id, // Makes the ship used
                'PLO_pilote' => TestPersonneSeeder::$pilot,
                'PLO_securite' => TestPersonneSeeder::$security,
                'PLO_directeur' => TestPersonneSeeder::$diveDirector
            ])->create();
            $response = $this->deleteJson("/api/bateaux/$ship->BAT_id");
            $response->assertStatus(200);

            self::assertNotNull( Bateau::find($ship->BAT_id) );
            $ship->refresh();
            self::assertFalse( $ship->BAT_active );
        } finally {
            DB::rollBack();
        }
    }
}
