<?php

namespace Tests\Feature;

use App\Models\Lieu;
use App\Models\Plongee;
use Database\Seeders\TestPersonneSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ApiLieuxTest extends TestCase
{
    use RefreshDatabase;

    public function test_getLieux()
    {
        $response = $this->getJson('/api/lieux');
        $response->assertStatus(200);
        $response->assertExactJson( [
            ["id"=>1,"libelle"=>"Baz Lucs", "description"=>'Plateau rocheux 0-30m', "actif"=>true],
            ["id"=>2,"libelle"=>"Île Verte", "description"=>'Gros éboulis rocheux 0-20m', "actif"=>true],
            ["id"=>3,"libelle"=>"Corbeau", "description"=>'Tombant 0-30m', "actif"=>true],
        ]);
    }
    public function test_getLieuxInactifs()
    {
        $response = $this->getJson('/api/lieux/inactifs');
        $response->assertStatus(200);
        $response->assertExactJson( [
            ["id"=>4,"libelle"=>"Ifs", "description"=>'Plongée dans les égouts 0-6m', "actif"=>false]
        ]);
    }
    public function test_getOneLieuOk()
    {
        DB::beginTransaction();
        try {
            /** @var Lieu $site */
            $site = Lieu::factory()->create();
            $response = $this->getJson("/api/lieux/$site->LIE_id");
            $response->assertStatus(200);
            $response->assertExactJson(
                ["id" => $site->LIE_id, "libelle" => $site->LIE_libelle, "description" => $site->LIE_description,
                    "actif" => $site->LIE_active]
            );
        } finally {
            DB::rollBack();
        }
    }
    public function test_getOneLieuFail()
    {
        $response = $this->getJson('/api/lieux/0');
        $response->assertStatus(404);
    }
    public function test_postOneLieuOk()
    {
        DB::beginTransaction();
        try {
            $response = $this->postJson("/api/lieux", ['libelle'=>'test', 'description'=>'Lorem Ipsum']);
            $response->assertStatus(200);
            $response->assertJsonFragment(
                ["libelle" => 'test', "description" => 'Lorem Ipsum', "actif" => true]
            );
            $id=$response->json('id');
            self::assertNotNull($id);
            $site = Lieu::find($id);
            self::assertNotNull($site);
            self::assertEquals('test', $site->LIE_libelle);
        } finally {
            DB::rollBack();
        }
    }
    public function test_putLieuOk() {
        DB::beginTransaction();
        try {
            /** @var Lieu $site */
            $site = Lieu::factory()->active()->create();
            $response = $this->putJson("/api/lieux/$site->LIE_id", ['libelle'=>'test', 'description'=>'Lorem Ipsum']);
            $response->assertStatus(200);
            $response->assertJsonFragment(
                ['libelle'=>'test', 'description'=>'Lorem Ipsum', "actif" => true]
            );
            $site->refresh();
            self::assertEquals('test', $site->LIE_libelle);
            self::assertEquals('Lorem Ipsum', $site->LIE_description);
        } finally {
            DB::rollBack();
        }
    }
    public function test_putLieuFail() {
        DB::beginTransaction();
        try {
            /** @var Lieu $site */
            $site = Lieu::factory()->active()->create();
            $response = $this->putJson("/api/lieux/$site->LIE_id", [
                'libelle'=>'testtesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttest', // too long
                'description'=>str_pad("too long", 127, "X")]); // too long
            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['libelle','description']);
        } finally {
            DB::rollBack();
        }
    }
    public function test_putLieuOkWithId() {
        DB::beginTransaction();
        try {
            /** @var Lieu $site */
            $site = Lieu::factory()->active()->create();
            $response = $this->putJson("/api/lieux", ['id'=>$site->LIE_id,'libelle'=>'test', 'description'=>'Lorem Ipsum']);
            $response->assertStatus(200);
            $response->assertJsonFragment(
                ["libelle" => 'test', 'description'=>'Lorem Ipsum', "actif" => true]
            );
            $site->refresh();
            self::assertEquals('test', $site->LIE_libelle);
            self::assertEquals('Lorem Ipsum', $site->LIE_description);
        } finally {
            DB::rollBack();
        }
    }
    public function test_putLieuFailWithId() {
        DB::beginTransaction();
        try {
            /** @var Lieu $site */
            $site = Lieu::factory()->active()->create();
            $response = $this->putJson("/api/lieux", [
                'id' => $site->LIE_id,
                'libelle'=>'testtesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttest', // too long
                'description'=>str_pad("too long", 127, "X")]); // too long
            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['libelle','description']);
        } finally {
            DB::rollBack();
        }
    }
    public function test_deleteOk() {
        DB::beginTransaction();
        try {
            /** @var Lieu $site */
            $site = Lieu::factory()->active()->create();
            $response = $this->deleteJson("/api/lieux/$site->LIE_id");
            $response->assertStatus(200);

            self::assertNull( Lieu::find($site->LIE_id) );
        } finally {
            DB::rollBack();
        }
    }
    public function test_deleteInactive() {
        DB::beginTransaction();
        try {
            /** @var Lieu $site */
            $site = Lieu::factory()->active()->create();
            TestPersonneSeeder::run(0);
            Plongee::factory()->state([
                'PLO_lieu' => $site->LIE_id, // Makes the site used
                'PLO_pilote' => TestPersonneSeeder::$pilot,
                'PLO_securite' => TestPersonneSeeder::$security,
                'PLO_directeur' => TestPersonneSeeder::$diveDirector
            ])->create();
            $response = $this->deleteJson("/api/lieux/$site->LIE_id");
            $response->assertStatus(200);

            self::assertNotNull( Lieu::find($site->LIE_id) );
            $site->refresh();
            self::assertFalse( $site->LIE_active );
        } finally {
            DB::rollBack();
        }
    }
}
