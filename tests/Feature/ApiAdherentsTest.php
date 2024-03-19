<?php

namespace Tests\Feature;

use App\Models\Adherent;
use App\Models\Participe;
use App\Models\Personne;
use App\Models\Plongee;
use Database\Seeders\TestPersonneSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Ramsey\Collection\Collection;
use Tests\TestCase;
use function PHPUnit\Framework\assertEquals;

class ApiAdherentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_getAdherentsEmpty()
    {
        $response = $this->getJson('/api/adherents');
        $response->assertStatus(200);
        $response->assertExactJson( [] );
    }
    public function test_getAdherentsOk()
    {
        DB::beginTransaction();
        try {
            /** @var Adherent[]|Collection $persons */
            $persons = Personne::factory()->active()->has(Adherent::factory())->count(7)->create();
            Personne::factory()->state(['PER_active' => false])->has(Adherent::factory())->count(4)->create();
            $response = $this->getJson('/api/adherents');
            $response->assertStatus(200);
            $response->assertJsonCount(7);
            $response->assertJsonStructure(['*' => ['id', 'licence', 'date_certificat_medical', 'forfait', 'niveau',
                'nom', 'prenom', 'email', 'actif']]);
            foreach ($persons as /** @var Personne $p */$p) {
                $response->assertJsonFragment(['id'=>$p->PER_id]);
            }
        } finally {
            DB::rollBack();
        }
    }
    public function test_getAdherentsOkWithDetails()
    {
        DB::beginTransaction();
        try {
            /** @var Adherent[]|Collection $persons */
            $persons = Personne::factory()->active()->has(Adherent::factory())->count(7)->create();
            Personne::factory()->state(['PER_active' => false])->has(Adherent::factory())->count(4)->create();
            $response = $this->getJson('/api/adherents/details');
            $response->assertStatus(200);
            $response->assertJsonCount(7);
            $response->assertJsonStructure(['*' => ['id', 'licence', 'date_certificat_medical', 'forfait', 'niveau',
                'nom', 'prenom', 'email', 'actif', 'niveau_code', 'profondeur_si_encadre','profondeur_si_autonome',
                'niveau_libelle']]);
            foreach ($persons as /** @var Personne $p */$p) {
                $response->assertJsonFragment(['id'=>$p->PER_id]);
            }
        } finally {
            DB::rollBack();
        }
    }
    public function test_getAdherentsInactives()
    {
        DB::beginTransaction();
        try {
            /** @var Adherent[]|Collection $persons */
            Personne::factory()->active()->has(Adherent::factory())->count(7)->create();
            $persons = Personne::factory()->state(['PER_active' => false])->has(Adherent::factory())->count(4)
                ->create();
            $response = $this->getJson('/api/adherents/inactifs');
            $response->assertStatus(200);
            $response->assertJsonCount(4);
            $response->assertJsonStructure(['*' => ['id', 'licence', 'date_certificat_medical', 'forfait', 'niveau',
                'nom', 'prenom', 'email', 'actif']]);
            foreach ($persons as /** @var Personne $p */$p) {
                $response->assertJsonFragment(['id'=>$p->PER_id]);
            }
        } finally {
            DB::rollBack();
        }
    }
    public function test_getOneAdherentOk()
    {
        DB::beginTransaction();
        try {
            /** @var Personne $pers */
            $pers = Personne::factory()->has(Adherent::factory())->create();
            $response = $this->getJson("/api/adherents/$pers->PER_id");
            $response->assertStatus(200);
            $response->assertExactJson(
                ["id" => $pers->PER_id, "licence" => $pers->adherent->ADH_licence,
                    "date_certificat_medical" => $pers->adherent->ADH_date_certificat,
                    'forfait' => $pers->adherent->ADH_forfait, 'niveau' => $pers->adherent->ADH_niveau,
                    "nom" => $pers->PER_nom, "prenom" => $pers->PER_prenom,
                    "email" => $pers->PER_email, "actif" => $pers->PER_active]
            );
        } finally {
            DB::rollBack();
        }
    }
    public function test_getOneAdherentOkWithDetails()
    {
        DB::beginTransaction();
        try {
            /** @var Personne $pers */
            $pers = Personne::factory()->has(Adherent::factory())->create();
            $response = $this->getJson("/api/adherents/$pers->PER_id/details");
            $response->assertStatus(200);
            $niveau = $pers->adherent->niveau;
            $response->assertExactJson(
                ["id" => $pers->PER_id, "licence" => $pers->adherent->ADH_licence,
                    "date_certificat_medical" => $pers->adherent->ADH_date_certificat,
                    'forfait' => $pers->adherent->ADH_forfait, 'niveau' => $pers->adherent->ADH_niveau,
                    "nom" => $pers->PER_nom, "prenom" => $pers->PER_prenom,
                    "email" => $pers->PER_email, "actif" => $pers->PER_active,
                    "niveau_code" => $niveau->NIV_code, "profondeur_si_encadre" => $niveau->NIV_prof_encadre,
                    "profondeur_si_autonome" => $niveau->NIV_prof_autonome,
                    "niveau_libelle" => $niveau->NIV_libelle
                ]);
        } finally {
            DB::rollBack();
        }
    }
    public function test_getOneAdherentFail()
    {
        $response = $this->getJson('/api/adherents/0');
        $response->assertStatus(404);
    }
    public function test_postOneAdherentOk()
    {
        DB::beginTransaction();
        try {
            $response = $this->postJson("/api/adherents", ['nom'=>'test', 'prenom'=>'tset',
                'email'=>'inconnu@iut.fr', 'pass'=>'AbCd!9876!', 'pass_confirmation'=>'AbCd!9876!',
                'licence'=>'1234567890', 'date_certificat_medical'=>'2023-10-09', 'forfait'=>'gold', 'niveau'=>10
                ]);
            $response->assertStatus(200);
            $response->assertJsonFragment(
                ['nom'=>'test', 'prenom'=>'tset', 'email'=>'inconnu@iut.fr', "actif" => true,
                    'licence'=>'1234567890', 'date_certificat_medical'=>'2023-10-09', 'forfait'=>'gold', 'niveau'=>10 ]
            );
            $id=$response->json('id');
            self::assertNotNull($id);
            /** @var Adherent $pers */
            $pers = Adherent::with('personne')->find($id);
            self::assertNotNull($pers);
            self::assertEquals('1234567890', $pers->ADH_licence);
            self::assertEquals('2023-10-09', $pers->ADH_date_certificat);
            self::assertEquals('gold', $pers->ADH_forfait);
            self::assertEquals('10', $pers->ADH_niveau);
            self::assertEquals('test', $pers->personne->PER_nom);
            self::assertEquals('tset', $pers->personne->PER_prenom);
            self::assertEquals('inconnu@iut.fr', $pers->personne->PER_email);
        } finally {
            DB::rollBack();
        }
    }

    public function test_putAdherentOkWithToken() {
        DB::beginTransaction();
        try {
            /** @var Personne $pers */
            $pers = Personne::factory()->has(Adherent::factory())->active()->create();
            $pers->setRememberToken(md5(rand(1, 1000) . microtime()));
            $response = $this->putJson("/api/adherents/$pers->PER_id", ['nom'=>'Lorem', 'prenom'=>'Ipsum',
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
    public function test_putAdherentOkWithOldPass() {
        DB::beginTransaction();
        try {
            /** @var Personne $pers */
            $pers = Personne::factory()->has(Adherent::factory())->active()->create();
            $response = $this->putJson("/api/adherents/$pers->PER_id", ['nom'=>'Lorem', 'prenom'=>'Ipsum',
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
            $pers = Personne::factory()->has(Adherent::factory())->active()->create();
            $response = $this->putJson("/api/adherents/$pers->PER_id", ['pass'=>'abcd-EFGH-1',
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
            $pers = Personne::factory()->has(Adherent::factory())->active()->create();
            $pers->setRememberToken("ABCD");
            $response = $this->putJson("/api/adherents/$pers->PER_id", ['pass' => 'abcd-EFGH-1',
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
    public function test_putAdherentFailAll() {
        DB::beginTransaction();
        try {
            /** @var Personne $pers */
            $pers = Personne::factory()->has(Adherent::factory())->active()->create();
            $response = $this->putJson("/api/adherents/$pers->PER_id", [
                'nom'=>str_pad("too long", 46, "X"),
                'prenom'=>str_pad("too long", 46, "X"),
                'token'=>$pers->getRememberToken(),
                'pass'=>'13',
                'pass_confirmation'=>'31',
                'email' => 'monAdresse',
                'licence' => str_pad("too long", 46, "X"),
                'date_certificat_medical' => "aujourd'hui",
                'niveau' => 128
            ]);
            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['nom', 'prenom', 'pass', 'email', 'licence', 'date_certificat_medical', 'niveau']);
        } finally {
            DB::rollBack();
        }
    }
    public function test_putAdherentFailBadConfirmation() {
        DB::beginTransaction();
        try {
            /** @var Personne $pers */
            $pers = Personne::factory()->has(Adherent::factory())->active()->create();
            $response = $this->putJson("/api/adherents/$pers->PER_id", [
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
    public function test_putAdherentFailNoConfirmation() {
        DB::beginTransaction();
        try {
            /** @var Personne $pers */
            $pers = Personne::factory()->has(Adherent::factory())->active()->create();
            $response = $this->putJson("/api/adherents/$pers->PER_id", [
                'token'=>$pers->getRememberToken(),
                'pass'=>'13aZ-ghZE',
            ]);
            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['pass']);
        } finally {
            DB::rollBack();
        }
    }
    public function test_putAdherentFailNoDigit() {
        DB::beginTransaction();
        try {
            /** @var Personne $pers */
            $pers = Personne::factory()->has(Adherent::factory())->active()->create();
            $response = $this->putJson("/api/adherents/$pers->PER_id", [
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
    public function test_putAdherentFailNoCaps() {
        DB::beginTransaction();
        try {
            /** @var Personne $pers */
            $pers = Personne::factory()->has(Adherent::factory())->active()->create();
            $response = $this->putJson("/api/adherents/$pers->PER_id", [
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
    public function test_putAdherentFailNoMins() {
        DB::beginTransaction();
        try {
            /** @var Personne $pers */
            $pers = Personne::factory()->has(Adherent::factory())->active()->create();
            $response = $this->putJson("/api/adherents/$pers->PER_id", [
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
    public function test_putAdherentFailNoSymbol() {
        DB::beginTransaction();
        try {
            /** @var Personne $pers */
            $pers = Personne::factory()->has(Adherent::factory())->active()->create();
            $response = $this->putJson("/api/adherents/$pers->PER_id", [
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
    public function test_putAdherentFailTooShort() {
        DB::beginTransaction();
        try {
            /** @var Personne $pers */
            $pers = Personne::factory()->has(Adherent::factory())->active()->create();
            $response = $this->putJson("/api/adherents/$pers->PER_id", [
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
    public function test_putAdherentOkWithOldPassUsingJsonId() {
        DB::beginTransaction();
        try {
            /** @var Personne $pers */
            $pers = Personne::factory()->has(Adherent::factory())->active()->create();
            $response = $this->putJson("/api/adherents", ['id'=>$pers->PER_id, 'nom'=>'Lorem', 'prenom'=>'Ipsum',
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
            $pers = Personne::factory()->has(Adherent::factory())->active()->create();
            $response = $this->deleteJson("/api/adherents/$pers->PER_id");
            $response->assertStatus(200);

            self::assertNull( Adherent::find($pers->PER_id) );
        } finally {
            DB::rollBack();
        }
    }
    public function test_deleteInactive() {
        DB::beginTransaction();
        try {
            TestPersonneSeeder::run(0);
            /** @var Personne $pers */
            $pers = Personne::factory()->has(Adherent::factory())->active()->create();
            /** @var Adherent $adherent */
            $adherent = $pers->adherent()->first();
            /** @var Plongee $dive */
            $dive = Plongee::factory()->state([
                'PLO_niveau' => $adherent->ADH_niveau,
                'PLO_pilote' => TestPersonneSeeder::$pilot,
                'PLO_securite' => TestPersonneSeeder::$security,
                'PLO_directeur' => TestPersonneSeeder::$diveDirector
            ])->create();

            Participe::create(['PAR_plongee'=>$dive->PLO_id, 'PAR_adherent'=>$adherent->ADH_id]);
            $response = $this->deleteJson("/api/adherents/" . $adherent->ADH_id);
            $response->assertStatus(200);

            $pers = Personne::find($adherent->ADH_id);
            self::assertNotNull($pers);
            $pers->refresh();
            self::assertFalse( $pers->PER_active );
        } finally {
            DB::rollBack();
        }
    }
    public function test_putAdherentFailsBadNiveau() {
        DB::beginTransaction();
        try {
            /** @var Personne $pers */
            $pers = Personne::factory()->has(Adherent::factory())->active()->create();
            $pers->setRememberToken(md5(rand(1, 1000) . microtime()));
            $response = $this->putJson("/api/adherents/$pers->PER_id", [
                'niveau'=>16, 'token'=>$pers->getRememberToken()]);
            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['niveau']);
        } finally {
            DB::rollBack();
        }
    }
    public function test_putAdherentFailsDuplicateLicence() {
        DB::beginTransaction();
        try {
            /** @var Personne $pers1 */
            $pers1 = Personne::factory()->has(Adherent::factory())->active()->create();
            /** @var Personne $pers2 */
            $pers2 = Personne::factory()->has(Adherent::factory())->active()->create();
            $pers2->setRememberToken(md5(rand(1, 1000) . microtime()));
            $response = $this->putJson("/api/adherents/$pers2->PER_id", [
                'licence'=>$pers1->adherent->ADH_licence, 'token'=>$pers2->getRememberToken()]);
            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['licence']);
        } finally {
            DB::rollBack();
        }
    }
    public function test_putAdherentFailsDuplicateName() {
        DB::beginTransaction();
        try {
            /** @var Personne $pers1 */
            $pers1 = Personne::factory()->has(Adherent::factory())->active()->create();
            /** @var Personne $pers2 */
            $pers2 = Personne::factory()->has(Adherent::factory())->active()->create();
            $pers2->setRememberToken(md5(rand(1, 1000) . microtime()));
            $response = $this->putJson("/api/adherents/$pers2->PER_id", ['nom'=>$pers1->PER_nom,
                'prenom'=>$pers1->PER_prenom, 'token'=>$pers2->getRememberToken()]);
            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['prenom']);
        } finally {
            DB::rollBack();
        }
    }
    public function test_putAdherentFailsDuplicateNameOnly() {
        DB::beginTransaction();
        try {
            /** @var Personne $pers1 */
            $pers1 = Personne::factory()->has(Adherent::factory())->active()->create();
            /** @var Personne $pers2 */
            $pers2 = Personne::factory()->has(Adherent::factory())->active()
                ->state(['PER_prenom'=>$pers1->PER_prenom])->create();
            assertEquals($pers1->PER_prenom, $pers2->PER_prenom);
            $pers2->setRememberToken(md5(rand(1, 1000) . microtime()));
            $response = $this->putJson("/api/adherents/$pers2->PER_id", ['nom'=>$pers1->PER_nom,
                'token'=>$pers2->getRememberToken()]);
            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['nom']);
        } finally {
            DB::rollBack();
        }
    }
    public function test_putAdherentFailsDuplicateSurnameOnly() {
        DB::beginTransaction();
        try {
            /** @var Personne $pers1 */
            $pers1 = Personne::factory()->has(Adherent::factory())->active()->create();
            /** @var Personne $pers2 */
            $pers2 = Personne::factory()->has(Adherent::factory())->active()
                ->state(['PER_nom'=>$pers1->PER_nom])->create();
            assertEquals($pers1->PER_nom, $pers2->PER_nom);
            $pers2->setRememberToken(md5(rand(1, 1000) . microtime()));
            $response = $this->putJson("/api/adherents/$pers2->PER_id", ['prenom'=>$pers1->PER_prenom,
                'token'=>$pers2->getRememberToken()]);
            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['prenom']);
        } finally {
            DB::rollBack();
        }
    }
    public function test_putAdherentFailsDuplicateEmail() {
        DB::beginTransaction();
        try {
            /** @var Personne $pers1 */
            $pers1 = Personne::factory()->has(Adherent::factory())->active()->create();
            /** @var Personne $pers2 */
            $pers2 = Personne::factory()->has(Adherent::factory())->active()->create();
            $pers2->setRememberToken(md5(rand(1, 1000) . microtime()));
            $response = $this->putJson("/api/adherents/$pers2->PER_id", ['email'=>$pers1->PER_email,
                'token'=>$pers2->getRememberToken()]);
            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['email']);
        } finally {
            DB::rollBack();
        }
    }

}
