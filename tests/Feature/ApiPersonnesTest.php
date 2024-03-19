<?php

namespace Tests\Feature;

use App\Models\Autorisations;
use App\Models\Personne;
use App\Models\Plongee;
use Database\Seeders\TestPersonneSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Ramsey\Collection\Collection;
use Tests\TestCase;
use function PHPUnit\Framework\assertEquals;

class ApiPersonnesTest extends TestCase
{
    use RefreshDatabase;

    public function test_getPersonnesInactives()
    {
        DB::beginTransaction();
        try {
            /** @var Personne[]|Collection $persons */
            Personne::factory()->count(7)->state(['PER_active' => true])->create();
            $persons = Personne::factory()->count(10)->state(['PER_active' => false])->create();
            $response = $this->getJson('/api/personnes/inactifs');
            $response->assertStatus(200);
            $response->assertJsonCount(10);
            $response->assertJsonStructure(['*' => ['id', 'nom', 'prenom', 'email', 'actif']]);
            foreach ($persons as /** @var Personne $p */$p) {
                $response->assertJsonFragment(['id'=>$p->PER_id]);
            }
        } finally {
            DB::rollBack();
        }
    }
    public function test_getOnePersonneOk()
    {
        DB::beginTransaction();
        try {
            /** @var Personne $pers */
            $pers = Personne::factory()->create();
            $response = $this->getJson("/api/personnes/$pers->PER_id");
            $response->assertStatus(200);
            $response->assertExactJson(
                ["id" => $pers->PER_id, "nom" => $pers->PER_nom, "prenom" => $pers->PER_prenom,
                    "email" => $pers->PER_email, "actif" => $pers->PER_active]
            );
        } finally {
            DB::rollBack();
        }
    }
    public function test_getOnePersonneFail()
    {
        $response = $this->getJson('/api/personnes/0');
        $response->assertStatus(404);
    }
    public function test_postOnePersonneOk()
    {
        DB::beginTransaction();
        try {
            $response = $this->postJson("/api/personnes", ['nom'=>'test', 'prenom'=>'tset',
                'email'=>'inconnu@iut.fr', 'pass'=>'AbCd!9876!', 'pass_confirmation'=>'AbCd!9876!']);
            $response->assertStatus(200);
            $response->assertJsonFragment(
                ['nom'=>'test', 'prenom'=>'tset', 'email'=>'inconnu@iut.fr', "actif" => true]
            );
            $id=$response->json('id');
            self::assertNotNull($id);
            $site = Personne::find($id);
            self::assertNotNull($site);
            self::assertEquals('test', $site->PER_nom);
            self::assertEquals('tset', $site->PER_prenom);
            self::assertEquals('inconnu@iut.fr', $site->PER_email);
        } finally {
            DB::rollBack();
        }
    }
    public function test_putPersonneOkWithToken() {
        DB::beginTransaction();
        try {
            /** @var Personne $pers */
            $pers = Personne::factory()->active()->create();
            $pers->setRememberToken(md5(rand(1, 1000) . microtime()));
            $response = $this->putJson("/api/personnes/$pers->PER_id", ['nom'=>'Lorem', 'prenom'=>'Ipsum',
                'email'=>'another@iut.com', 'token'=>$pers->getRememberToken()]);
            $response->assertStatus(200);
            $response->assertJsonFragment(
                ['nom'=>'Lorem', 'prenom'=>'Ipsum', 'email'=>'another@iut.com', "actif" => true]
            );
            $pers->refresh();
            self::assertEquals('Lorem', $pers->PER_nom);
            self::assertEquals('Ipsum', $pers->PER_prenom);
            self::assertEquals('another@iut.com', $pers->PER_email);
        } finally {
            DB::rollBack();
        }
    }
    public function test_putPersonneOkWithOldPass() {
        DB::beginTransaction();
        try {
            /** @var Personne $pers */
            $pers = Personne::factory()->active()->create();
            $response = $this->putJson("/api/personnes/$pers->PER_id", ['nom'=>'Lorem', 'prenom'=>'Ipsum',
                'email'=>'another@iut.com', 'old_pass'=>$pers->getRememberToken()]); // Factory sets clear pass in token
            $response->assertStatus(200);
            $response->assertJsonFragment(
                ['nom'=>'Lorem', 'prenom'=>'Ipsum', 'email'=>'another@iut.com', "actif" => true]
            );
            $pers->refresh();
            self::assertEquals('Lorem', $pers->PER_nom);
            self::assertEquals('Ipsum', $pers->PER_prenom);
            self::assertEquals('another@iut.com', $pers->PER_email);
        } finally {
            DB::rollBack();
        }
    }
    public function test_changePasswordWithOldPass() {
        DB::beginTransaction();
        try {
            /** @var Personne $pers */
            $pers = Personne::factory()->active()->create();
            $response = $this->putJson("/api/personnes/$pers->PER_id", ['pass'=>'abcd-EFGH-1',
                'pass_confirmation'=>'abcd-EFGH-1',
                'old_pass'=>$pers->getRememberToken()]); // Factory sets clear pass in token
            $response->assertStatus(200);
            $response->assertJsonFragment(
                ['nom'=>$pers->PER_nom, 'prenom'=>$pers->PER_prenom, 'email'=>$pers->PER_email,
                    "actif" => $pers->PER_active]
            );
            $pers->refresh();
            self::assertTrue(Hash::check('abcd-EFGH-1', $pers->PER_pass));
        } finally {
            DB::rollBack();
        }
    }
    public function test_changePasswordWithToken()
    {
        DB::beginTransaction();
        try {
            /** @var Personne $pers */
            $pers = Personne::factory()->create();
            $pers->setRememberToken("ABCD");
            $response = $this->putJson("/api/personnes/$pers->PER_id", ['pass' => 'abcd-EFGH-1',
                'pass_confirmation' => 'abcd-EFGH-1',
                'token' => $pers->getRememberToken()]); // Factory sets clear pass in token
            $response->assertStatus(200);
            $response->assertJsonFragment(
                ['nom' => $pers->PER_nom, 'prenom' => $pers->PER_prenom, 'email' => $pers->PER_email,
                    "actif" => $pers->PER_active]
            );
            $pers->refresh();
            self::assertTrue(Hash::check('abcd-EFGH-1', $pers->PER_pass));
        } finally {
            DB::rollBack();
        }
    }
    public function test_putPersonneFail() {
        DB::beginTransaction();
        try {
            /** @var Personne $pers */
            $pers = Personne::factory()->active()->create();
            $response = $this->putJson("/api/personnes/$pers->PER_id", [
                'nom'=>str_pad("too long", 46, "X"),
                'prenom'=>str_pad("too long", 46, "X"),
                'token'=>$pers->getRememberToken(),
                'pass'=>'13',
                'pass_confirmation'=>'31',
                'email' => 'monAdresse'
            ]);
            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['nom', 'prenom', 'pass', 'email']);
        } finally {
            DB::rollBack();
        }
    }
    public function test_putPersonneFailBadConfirmation() {
        DB::beginTransaction();
        try {
            /** @var Personne $pers */
            $pers = Personne::factory()->active()->create();
            $response = $this->putJson("/api/personnes/$pers->PER_id", [
                'token'=>$pers->getRememberToken(),
                'pass'=>'13aZ-ghZE',
                'pass_confirmation'=>'13aZ-ghZEF',
            ]);
            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['pass']);
        } finally {
            DB::rollBack();
        }
    }
    public function test_putPersonneFailNoConfirmation() {
        DB::beginTransaction();
        try {
            /** @var Personne $pers */
            $pers = Personne::factory()->active()->create();
            $response = $this->putJson("/api/personnes/$pers->PER_id", [
                'token'=>$pers->getRememberToken(),
                'pass'=>'13aZ-ghZE',
            ]);
            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['pass']);
        } finally {
            DB::rollBack();
        }
    }
    public function test_putPersonneFailNoDigit() {
        DB::beginTransaction();
        try {
            /** @var Personne $pers */
            $pers = Personne::factory()->active()->create();
            $response = $this->putJson("/api/personnes/$pers->PER_id", [
                'token'=>$pers->getRememberToken(),
                'pass'=>'TRaZ-ghZE',
                'pass_confirmation'=>'TRaZ-ghZE',
            ]);
            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['pass']);
        } finally {
            DB::rollBack();
        }
    }
    public function test_putPersonneFailNoCaps() {
        DB::beginTransaction();
        try {
            /** @var Personne $pers */
            $pers = Personne::factory()->active()->create();
            $response = $this->putJson("/api/personnes/$pers->PER_id", [
                'token'=>$pers->getRememberToken(),
                'pass'=>'13az-ghze',
                'pass_confirmation'=>'13az-ghze',
            ]);
            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['pass']);
        } finally {
            DB::rollBack();
        }
    }
    public function test_putPersonneFailNoMins() {
        DB::beginTransaction();
        try {
            /** @var Personne $pers */
            $pers = Personne::factory()->active()->create();
            $response = $this->putJson("/api/personnes/$pers->PER_id", [
                'token'=>$pers->getRememberToken(),
                'pass'=>'13AZ-GHZE',
                'pass_confirmation'=>'13AZ-GHZE',
            ]);
            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['pass']);
        } finally {
            DB::rollBack();
        }
    }
    public function test_putPersonneFailNoSymbol() {
        DB::beginTransaction();
        try {
            /** @var Personne $pers */
            $pers = Personne::factory()->active()->create();
            $response = $this->putJson("/api/personnes/$pers->PER_id", [
                'token'=>$pers->getRememberToken(),
                'pass'=>'13aZghZE',
                'pass_confirmation'=>'13aZghZEF',
            ]);
            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['pass']);
        } finally {
            DB::rollBack();
        }
    }
    public function test_putPersonneFailTooShort() {
        DB::beginTransaction();
        try {
            /** @var Personne $pers */
            $pers = Personne::factory()->active()->create();
            $response = $this->putJson("/api/personnes/$pers->PER_id", [
                'token'=>$pers->getRememberToken(),
                'pass'=>'13aZ-12',
                'pass_confirmation'=>'13aZ-12',
            ]);
            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['pass']);
        } finally {
            DB::rollBack();
        }
    }
    public function test_putPersonneOkWithOldPassUsingJsonId() {
        DB::beginTransaction();
        try {
            /** @var Personne $pers */
            $pers = Personne::factory()->active()->create();
            $response = $this->putJson("/api/personnes", ['id'=>$pers->PER_id, 'nom'=>'Lorem', 'prenom'=>'Ipsum',
                'email'=>'another@iut.com', 'old_pass'=>$pers->getRememberToken()]); // Factory sets clear pass in token
            $response->assertStatus(200);
            $response->assertJsonFragment(
                ['nom'=>'Lorem', 'prenom'=>'Ipsum', 'email'=>'another@iut.com', "actif" => true]
            );
            $pers->refresh();
            self::assertEquals('Lorem', $pers->PER_nom);
            self::assertEquals('Ipsum', $pers->PER_prenom);
            self::assertEquals('another@iut.com', $pers->PER_email);
        } finally {
            DB::rollBack();
        }
    }
    public function test_deleteOk() {
        DB::beginTransaction();
        try {
            /** @var Personne $pers */
            $pers = Personne::factory()->active()->create();
            $response = $this->deleteJson("/api/personnes/$pers->PER_id");
            $response->assertStatus(200);

            self::assertNull( Personne::find($pers->PER_id) );
        } finally {
            DB::rollBack();
        }
    }
    public function test_deleteInactive() {
        DB::beginTransaction();
        try {
            TestPersonneSeeder::run(0);
            Plongee::factory()->state([
                'PLO_pilote' => TestPersonneSeeder::$pilot,
                'PLO_securite' => TestPersonneSeeder::$security,
                'PLO_directeur' => TestPersonneSeeder::$diveDirector
            ])->create();
            $response = $this->deleteJson("/api/personnes/".TestPersonneSeeder::$diveDirector);
            $response->assertStatus(200);

            $pers = Personne::find(TestPersonneSeeder::$diveDirector);
            self::assertNotNull($pers);
            $pers->refresh();
            self::assertFalse( $pers->PER_active );
        } finally {
            DB::rollBack();
        }
    }
    public function test_putPersonneFailsDuplicateName() {
        DB::beginTransaction();
        try {
            /** @var Personne $pers1 */
            $pers1 = Personne::factory()->active()->create();
            /** @var Personne $pers2 */
            $pers2 = Personne::factory()->active()->create();
            $pers2->setRememberToken(md5(rand(1, 1000) . microtime()));
            $response = $this->putJson("/api/personnes/$pers2->PER_id", ['nom'=>$pers1->PER_nom,
                'prenom'=>$pers1->PER_prenom, 'token'=>$pers2->getRememberToken()]);
            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['prenom']);
        } finally {
            DB::rollBack();
        }
    }
    public function test_putPersonneFailsDuplicateNameOnly() {
        DB::beginTransaction();
        try {
            /** @var Personne $pers1 */
            $pers1 = Personne::factory()->active()->create();
            /** @var Personne $pers2 */
            $pers2 = Personne::factory()->active()->state(['PER_prenom'=>$pers1->PER_prenom])->create();
            assertEquals($pers1->PER_prenom, $pers2->PER_prenom);
            $pers2->setRememberToken(md5(rand(1, 1000) . microtime()));
            $response = $this->putJson("/api/personnes/$pers2->PER_id", ['nom'=>$pers1->PER_nom,
                'token'=>$pers2->getRememberToken()]);
            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['nom']);
        } finally {
            DB::rollBack();
        }
    }
    public function test_putPersonneFailsDuplicateSurnameOnly() {
        DB::beginTransaction();
        try {
            /** @var Personne $pers1 */
            $pers1 = Personne::factory()->active()->create();
            /** @var Personne $pers2 */
            $pers2 = Personne::factory()->active()->state(['PER_nom'=>$pers1->PER_nom])->create();
            assertEquals($pers1->PER_nom, $pers2->PER_nom);
            $pers2->setRememberToken(md5(rand(1, 1000) . microtime()));
            $response = $this->putJson("/api/personnes/$pers2->PER_id", ['prenom'=>$pers1->PER_prenom,
                'token'=>$pers2->getRememberToken()]);
            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['prenom']);
        } finally {
            DB::rollBack();
        }
    }
    public function test_putPersonneFailsDuplicateEmail() {
        DB::beginTransaction();
        try {
            /** @var Personne $pers1 */
            $pers1 = Personne::factory()->active()->create();
            /** @var Personne $pers2 */
            $pers2 = Personne::factory()->active()->create();
            $pers2->setRememberToken(md5(rand(1, 1000) . microtime()));
            $response = $this->putJson("/api/personnes/$pers2->PER_id", ['email'=>$pers1->PER_email,
                'token'=>$pers2->getRememberToken()]);
            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['email']);
        } finally {
            DB::rollBack();
        }
    }

    public function test_getPersonnesOk()
    {
        DB::beginTransaction();
        try {
            /** @var Personne[]|Collection $persons */
            $persons = Personne::factory()->count(7)->state(['PER_active' => true])->create();
            Personne::factory()->count(4)->state(['PER_active' => false])->create();
            $response = $this->getJson('/api/personnes');
            $response->assertStatus(200);
            $response->assertJsonCount(8); // plus super-admin
            $response->assertJsonStructure(['*' => ['id', 'nom', 'prenom', 'email', 'actif']]);
            foreach ($persons as /** @var Personne $p */$p) {
                $response->assertJsonFragment(['id'=>$p->PER_id]);
            }
        } finally {
            DB::rollBack();
        }
    }

    /* Test - Valeur : 6 */

    public function test_putPersonnePilot()
    {
        DB::beginTransaction();
        try {
            /** @var Personne $personne */
            $personne = Personne::factory()->active()->create();
            $response = self::putJson("api/personnes/$personne->PER_id", ['AUT_pilote' => true]);
            $response->assertStatus(200);
            $personne->refresh();
            self::assertEquals(true, $personne->isPilot());
        } finally {
            DB::rollBack();
        }
    }
}
