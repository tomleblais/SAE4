<?php

namespace Tests\Feature;

use App\Models\Adherent;
use App\Models\Bateau;
use App\Models\Lieu;
use App\Models\Palanquee;
use App\Models\Plongee;
use App\Models\Personne;
use Database\Seeders\TestPersonneSeeder;
use Database\Seeders\TestPlongeeSeeder;
use DateTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ApiPlongeesTest extends TestCase
{
    use RefreshDatabase;

    public function test_getPlongeesEmpty()
    {
        $response = $this->getJson('/api/plongees');
        $response->assertStatus(200);
        $response->assertExactJson( [] );
    }
    public function test_getPlongeesOk()
    {
        DB::beginTransaction();
        try {
            TestPlongeeSeeder::run(3,10, true);
            TestPlongeeSeeder::run(2,10, false);
            $response = $this->getJson('/api/plongees');
            $response->assertStatus(200);
            $response->assertJsonCount(3);
            $response->assertJsonStructure(['*' => ['id', 'lieu', 'bateau', 'date', 'moment',
                'min_plongeurs', 'max_plongeurs', 'niveau', 'active', 'etat', 'pilote', 'securite_de_surface',
                'directeur_de_plongee']]);
            $dives = Plongee::where("PLO_active", 1)->get();
            foreach ($dives as /** @var Plongee $p */$p) {
                $response->assertJsonFragment(['id'=>$p->PLO_id]);
            }
        } finally {
            DB::rollBack();
        }
    }
    public function test_getPlongeesOkWithDetails()
    {
        DB::beginTransaction();
        try {
            TestPlongeeSeeder::run(3,10, true);
            TestPlongeeSeeder::run(2,10, false);
            $response = $this->getJson('/api/plongees/details');
            $response->assertStatus(200);
            $response->assertJsonCount(3);
            $response->assertJsonStructure(['*' => ['id', 'lieu', 'bateau', 'date', 'moment',
                'min_plongeurs', 'max_plongeurs', 'niveau', 'active', 'etat', 'pilote', 'securite_de_surface',
                'directeur_de_plongee', 'moment_libelle', 'bateau_libelle', 'bateau_max_personnes',
                'lieu_libelle', 'lieu_description', 'niveau_code', 'niveau_libelle', 'niveau_profondeur_autonome',
                'niveau_profondeur_encadre', 'pilote_nom', 'pilote_prenom', 'securite_nom', 'securite_prenom']]);
            $dives = Plongee::where("PLO_active", 1)->get();
            foreach ($dives as /** @var Plongee $p */$p) {
                $response->assertJsonFragment(['id'=>$p->PLO_id]);
            }
        } finally {
            DB::rollBack();
        }
    }
    public function test_getPlongeesInactives()
    {
        DB::beginTransaction();
        try {
            TestPlongeeSeeder::run(3,10, true);
            TestPlongeeSeeder::run(2,10, false);
            $response = $this->getJson('/api/plongees/inactives');
            $response->assertStatus(200);
            $response->assertJsonCount(2);
            $response->assertJsonStructure(['*' => ['id', 'lieu', 'bateau', 'date', 'moment',
                'min_plongeurs', 'max_plongeurs', 'niveau', 'active', 'etat', 'pilote', 'securite_de_surface',
                'directeur_de_plongee']]);
            $dives = Plongee::where("PLO_active", 0)->get();
            foreach ($dives as /** @var Plongee $p */$p) {
                $response->assertJsonFragment(['id'=>$p->PLO_id]);
            }
        } finally {
            DB::rollBack();
        }
    }
    public function test_getOnePlongeeOk()
    {
        DB::beginTransaction();
        try {
            TestPlongeeSeeder::run(1, 10);
            /** @var Plongee $dive */
            $dive = Plongee::all()->first();
            $response = $this->getJson("/api/plongees/$dive->PLO_id");
            $response->assertStatus(200);
            $response->assertJsonStructure(['id', 'lieu', 'bateau', 'date', 'moment',
                'min_plongeurs', 'max_plongeurs', 'niveau', 'active', 'etat', 'pilote', 'securite_de_surface',
                'directeur_de_plongee']);

            $response->assertExactJson([
                    "id" => $dive->PLO_id, 'lieu' => $dive->PLO_lieu, 'bateau' => $dive->PLO_bateau,
                    'date' => $dive->getDate(), 'moment' => $dive->PLO_moment,
                    'min_plongeurs' => $dive->PLO_min_plongeurs, 'max_plongeurs' => $dive->PLO_max_plongeurs,
                    'niveau' => $dive->PLO_niveau, 'active' => $dive->PLO_active, 'etat' => $dive->PLO_etat,
                    'pilote' => $dive->PLO_pilote, 'securite_de_surface' => $dive->PLO_securite,
                    'directeur_de_plongee' => $dive->PLO_directeur]
            );
        } finally {
            DB::rollBack();
        }
    }
    public function test_getOnePlongeeOkWithDetails()
    {
        DB::beginTransaction();
        try {
            TestPlongeeSeeder::run(1, 10);
            /** @var Plongee $dive */
            $dive = Plongee::all()->first();
            $response = $this->getJson("/api/plongees/$dive->PLO_id/details");
            $response->assertStatus(200);
            $response->assertJsonStructure(['id', 'lieu', 'bateau', 'date', 'moment',
                'min_plongeurs', 'max_plongeurs', 'niveau', 'active', 'etat', 'pilote', 'securite_de_surface',
                'directeur_de_plongee','moment_libelle', 'bateau_libelle', 'bateau_max_personnes',
                'lieu_libelle', 'lieu_description', 'niveau_code', 'niveau_libelle', 'niveau_profondeur_autonome',
                'niveau_profondeur_encadre', 'pilote_nom', 'pilote_prenom', 'securite_nom', 'securite_prenom']);

            $response->assertExactJson([
                    "id" => $dive->PLO_id, 'lieu' => $dive->PLO_lieu, 'bateau' => $dive->PLO_bateau,
                    'date' => $dive->getDate(), 'moment' => $dive->PLO_moment,
                    'min_plongeurs' => $dive->PLO_min_plongeurs, 'max_plongeurs' => $dive->PLO_max_plongeurs,
                    'niveau' => $dive->PLO_niveau, 'active' => $dive->PLO_active, 'etat' => $dive->PLO_etat,
                    'pilote' => $dive->PLO_pilote, 'securite_de_surface' => $dive->PLO_securite,
                    'directeur_de_plongee' => $dive->PLO_directeur, 'moment_libelle' => $dive->moment->MOM_libelle,
                    'bateau_libelle' => $dive->bateau->BAT_libelle,
                    'bateau_max_personnes' => $dive->bateau->BAT_max_personnes,
                    'lieu_libelle' => $dive->lieu->LIE_libelle, 'lieu_description' => $dive->lieu->LIE_description,
                    'niveau_code' => $dive->niveau->NIV_code, 'niveau_libelle' => $dive->niveau->NIV_libelle,
                    'niveau_profondeur_autonome' => $dive->niveau->NIV_prof_autonome,
                    'niveau_profondeur_encadre' => $dive->niveau->NIV_prof_encadre,
                    'pilote_nom' => $dive->pilote->PER_nom, 'pilote_prenom' => $dive->pilote->PER_prenom,
                    'securite_nom' => $dive->securite->PER_nom, 'securite_prenom' => $dive->securite->PER_prenom]
            );
        } finally {
            DB::rollBack();
        }
    }
    public function test_getOnePlongeeFail()
    {
        $response = $this->getJson('/api/plongees/0');
        $response->assertStatus(404);
    }
    public function test_postOnePlongeeOk()
    {
        DB::beginTransaction();
        try {
            TestPlongeeSeeder::run(0, 0);
            $response = $this->postJson("/api/plongees", ['lieu' => 1, 'bateau' => 2,
                    'date' => "2023-10-10", 'moment' => 3,
                    'min_plongeurs' => 4, 'max_plongeurs' => 10,
                    'niveau' => 5,
                    'pilote' => TestPersonneSeeder::$pilot, 'securite_de_surface' => TestPersonneSeeder::$security,
                    'directeur_de_plongee'=> TestPersonneSeeder::$diveDirector
                ]);
            $response->assertStatus(200);
            $response->assertJsonFragment([
                'lieu' => 1, 'bateau' => 2,'date' => "2023-10-10", 'moment' => 3, 'min_plongeurs' => 4,
                    'max_plongeurs' => 10, 'niveau' => 5, 'pilote' => TestPersonneSeeder::$pilot,
                    'securite_de_surface' => TestPersonneSeeder::$security,
                    'directeur_de_plongee'=> TestPersonneSeeder::$diveDirector,
                    'active' => true, 'etat'=>1]
            );
            $id=$response->json('id');
            self::assertNotNull($id);
            $dive = Plongee::find($id);
            self::assertNotNull($dive);
            self::assertEquals(1, $dive->PLO_lieu);
            self::assertEquals(2, $dive->PLO_bateau);
            self::assertEquals(new DateTime('2023-10-10'), $dive->PLO_date);
            self::assertEquals(3, $dive->PLO_moment);
            self::assertEquals(4, $dive->PLO_min_plongeurs);
            self::assertEquals(10, $dive->PLO_max_plongeurs);
            self::assertEquals(5, $dive->PLO_niveau);
            self::assertEquals(TestPersonneSeeder::$pilot, $dive->PLO_pilote);
            self::assertEquals(TestPersonneSeeder::$security, $dive->PLO_securite);
            self::assertEquals(TestPersonneSeeder::$diveDirector, $dive->PLO_directeur);
            self::assertTrue($dive->PLO_active);
            self::assertEquals(1, $dive->PLO_etat);
        } finally {
            DB::rollBack();
        }
    }

    public function test_putPlongeeOk() {
        DB::beginTransaction();
        try {
            TestPlongeeSeeder::run(1, 10);
            /** @var Plongee $dive */
            $dive = Plongee::all()->first();
            /** @var Lieu $site */
            $site = Lieu::factory()->create();
            /** @var Bateau $ship */
            $ship = Bateau::factory()->create();
            DB::statement("INSERT INTO `PLO_MOMENTS` VALUES (4,'Jamais');");
            DB::statement("INSERT INTO `PLO_NIVEAUX` VALUES (15,'PN','Plongeur Nul',1,0,0,0,0)");
            TestPersonneSeeder::run(0); // create new admins
            $response = $this->putJson("/api/plongees/$dive->PLO_id", ['lieu' => $site->LIE_id,
                'bateau' => $ship->BAT_id,
                'date' => "0000-12-25", 'moment' => 4,
                'min_plongeurs' => 5, 'max_plongeurs' => 20,
                'niveau' => 15,
                'pilote' => TestPersonneSeeder::$pilot, 'securite_de_surface' => TestPersonneSeeder::$security,
                'directeur_de_plongee'=> TestPersonneSeeder::$diveDirector
            ]);
            $response->assertStatus(200);
            $response->assertJsonStructure(['id', 'lieu', 'bateau', 'date', 'moment',
                'min_plongeurs', 'max_plongeurs', 'niveau', 'active', 'etat', 'pilote', 'securite_de_surface',
                'directeur_de_plongee']);

            $response->assertExactJson([
                    "id" => $dive->PLO_id, 'lieu' => $site->LIE_id, 'bateau' => $ship->BAT_id,
                    'date' => "0000-12-25", 'moment' => 4,
                    'min_plongeurs' => 5, 'max_plongeurs' => 20,
                    'niveau' => 15, 'active' => $dive->PLO_active, 'etat' => $dive->PLO_etat,
                    'pilote' => TestPersonneSeeder::$pilot,
                    'securite_de_surface' => TestPersonneSeeder::$security,
                    'directeur_de_plongee' => TestPersonneSeeder::$diveDirector]
            );
        } finally {
            DB::rollBack();
        }
    }
    public function test_putPlongeeFailAll() {
        DB::beginTransaction();
        try {
            TestPlongeeSeeder::run(1, 10);
            /** @var Plongee $dive */
            $dive = Plongee::all()->first();
            $response = $this->putJson("/api/plongees/$dive->PLO_id", ['lieu' => 0,
                'bateau' => 0,
                'date' => "25/12/0000", 'moment' => 0,
                'min_plongeurs' => 1, 'max_plongeurs' => 0,
                'niveau' => 15,
                'pilote' => TestPersonneSeeder::$security, 'securite_de_surface' => TestPersonneSeeder::$secretary,
                'directeur_de_plongee'=> 0
            ]);
            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['lieu', 'bateau', 'date', 'min_plongeurs', 'max_plongeurs', 'niveau',
                'pilote', 'securite_de_surface', 'directeur_de_plongee']);
        } finally {
            DB::rollBack();
        }
    }
    public function test_putPlongeeOkWithId() {
        DB::beginTransaction();
        try {
            TestPlongeeSeeder::run(1, 10);
            /** @var Plongee $dive */
            $dive = Plongee::all()->first();
            /** @var Lieu $site */
            $site = Lieu::factory()->create();
            /** @var Bateau $ship */
            $ship = Bateau::factory()->create();
            DB::statement("INSERT INTO `PLO_MOMENTS` VALUES (4,'Jamais');");
            DB::statement("INSERT INTO `PLO_NIVEAUX` VALUES (15,'PN','Plongeur Nul',1,0,0,0,0)");
            TestPersonneSeeder::run(0); // create new admins
            $response = $this->putJson("/api/plongees", ['id'=>$dive->PLO_id, 'lieu' => $site->LIE_id,
                'bateau' => $ship->BAT_id,
                'date' => "0000-12-25", 'moment' => 4,
                'min_plongeurs' => 5, 'max_plongeurs' => 20,
                'niveau' => 15,
                'pilote' => TestPersonneSeeder::$pilot, 'securite_de_surface' => TestPersonneSeeder::$security,
                'directeur_de_plongee'=> TestPersonneSeeder::$diveDirector
            ]);
            $response->assertStatus(200);
            $response->assertJsonStructure(['id', 'lieu', 'bateau', 'date', 'moment',
                'min_plongeurs', 'max_plongeurs', 'niveau', 'active', 'etat', 'pilote', 'securite_de_surface',
                'directeur_de_plongee']);

            $response->assertExactJson([
                    "id" => $dive->PLO_id, 'lieu' => $site->LIE_id, 'bateau' => $ship->BAT_id,
                    'date' => "0000-12-25", 'moment' => 4,
                    'min_plongeurs' => 5, 'max_plongeurs' => 20,
                    'niveau' => 15, 'active' => $dive->PLO_active, 'etat' => $dive->PLO_etat,
                    'pilote' => TestPersonneSeeder::$pilot,
                    'securite_de_surface' => TestPersonneSeeder::$security,
                    'directeur_de_plongee' => TestPersonneSeeder::$diveDirector]
            );
        } finally {
            DB::rollBack();
        }
    }
    public function test_putPlongeeFailAllWithId() {
        DB::beginTransaction();
        try {
            TestPlongeeSeeder::run(1, 10);
            /** @var Plongee $dive */
            $dive = Plongee::all()->first();
            $response = $this->putJson("/api/plongees", ['id'=>$dive->PLO_id, 'lieu' => 0,
                'bateau' => 0,
                'date' => "25/12/0000", 'moment' => 0,
                'min_plongeurs' => 1, 'max_plongeurs' => 0,
                'niveau' => 15,
                'pilote' => TestPersonneSeeder::$security, 'securite_de_surface' => TestPersonneSeeder::$secretary,
                'directeur_de_plongee'=> 0
            ]);
            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['lieu', 'bateau', 'date', 'min_plongeurs', 'max_plongeurs', 'niveau',
                'pilote', 'securite_de_surface', 'directeur_de_plongee']);
        } finally {
            DB::rollBack();
        }
    }
    public function test_deletePlongeeActive() {
        DB::beginTransaction();
        try {
            TestPlongeeSeeder::run(1, 10);
            /** @var Plongee $dive */
            $dive = Plongee::all()->first();
            $dive->PLO_active = true;
            $dive->save();
            $response = $this->deleteJson("/api/plongees/$dive->PLO_id");
            $response->assertStatus(200);

            self::assertNotNull( Plongee::find($dive->PLO_id) );
            $dive->refresh();
            self::assertFalse($dive->PLO_active);
        } finally {
            DB::rollBack();
        }
    }
    public function test_deletePlongeeInactiveFail() {
        DB::beginTransaction();
        try {
            TestPlongeeSeeder::run(1, 10);
            /** @var Plongee $dive */
            $dive = Plongee::all()->first();
            $dive->PLO_active = false;
            $dive->save();
            $response = $this->deleteJson("/api/plongees/$dive->PLO_id");
            $response->assertStatus(500);

            self::assertNotNull( Plongee::find($dive->PLO_id) );
            $dive->refresh();
            self::assertFalse($dive->PLO_active);
        } finally {
            DB::rollBack();
        }
    }
    public function test_deletePlongeeInactivePassed() {
        DB::beginTransaction();
        try {
            TestPlongeeSeeder::run(1, 10);
            /** @var Plongee $dive */
            $dive = Plongee::all()->first();
            $dive->PLO_active = false;
            $dive->PLO_date = new DateTime('-10 year');
            $dive->save();
            $response = $this->deleteJson("/api/plongees/$dive->PLO_id");
            $response->assertStatus(200);

            self::assertNull( Plongee::find($dive->PLO_id) );
        } finally {
            DB::rollBack();
        }
    }

    public function test_getParticipantsOk() {
        DB::beginTransaction();
        try {
            // create at least 10 adherent with max level to be sure there will be divers in this dive
            Personne::factory()->active()->has(Adherent::factory()->state(['ADH_niveau' => 14]))->count(10)->create();
            TestPlongeeSeeder::run(1, 0);
            /** @var Plongee $dive */
            $dive = Plongee::all()->first();
            $response = $this->get("/api/plongees/$dive->PLO_id/participants");
            $response->assertStatus(200);

            $participants = $dive->participants();
            $response->assertJsonCount($participants->count());
            $response->assertJsonStructure(['*' => ['id', 'licence', 'date_certificat_medical', 'forfait', 'niveau',
                'nom', 'prenom', 'email', 'actif', 'niveau_code', 'profondeur_si_encadre','profondeur_si_autonome',
                'niveau_libelle']]);
            foreach ($participants as /** @var Adherent $participant */ $participant)
                $response->assertJsonFragment(['id'=>$participant->ADH_id]);
        } finally {
            DB::rollBack();
        }
    }
    public function test_getParticipantsFail() {
        DB::beginTransaction();
        try {
            $response = $this->get("/api/plongees/1234567789/participants");
            $response->assertStatus(404);
        } finally {
            DB::rollBack();
        }
    }
    public function test_deleteParticipantsOk() {
        DB::beginTransaction();
        try {
            // create at least 10 adherent with max level to be sure there will be divers in this dive
            Personne::factory()->active()->has(Adherent::factory()->state(['ADH_niveau' => 14]))->count(10)->create();
            TestPlongeeSeeder::run(1, 0, null, 4);
            /** @var Plongee $dive */
            $dive = Plongee::all()->first();
            $participants = $dive->participants;
            /** @var Adherent $victim */
            $victim = $participants->first();
            $response = $this->deleteJson("/api/plongees/$dive->PLO_id/participants/$victim->ADH_id");
            $response->assertStatus(200);

            $newDive = Plongee::find($dive->PLO_id);
            $newParticipants = $newDive->participants;
            self::assertCount($participants->count() - 1, $newParticipants);

            $old_ids = $participants->flatMap(fn($adh)=>$adh->ADH_id);
            $new_ids = $newParticipants->flatMap(fn($adh)=>$adh->ADH_id);
            foreach ($new_ids as $id)
                self::assertContains($id, $old_ids);
        } finally {
            DB::rollBack();
        }
    }
    public function test_deleteParticipantsFail() {
        DB::beginTransaction();
        try {
            // create at least 10 adherent with max level to be sure there will be divers in this dive
            Personne::factory()->active()->has(Adherent::factory()->state(['ADH_niveau' => 14]))->count(10)->create();
            TestPlongeeSeeder::run(1, 0);
            /** @var Plongee $dive */
            $dive = Plongee::all()->first();
            $response = $this->deleteJson("/api/plongees/$dive->PLO_id/participants/0");
            $response->assertStatus(404);
        } finally {
            DB::rollBack();
        }
    }


    public function test_getPalanqueesOk() {
        DB::beginTransaction();
        try {
            // create at least 10 adherent with max level to be sure there will be divers in this dive
            Personne::factory()->active()->has(Adherent::factory()->state(['ADH_niveau' => 14]))->count(10)->create();
            TestPlongeeSeeder::run(1, 0);
            /** @var Plongee $dive */
            $dive = Plongee::all()->first();
            $response = $this->get("/api/plongees/$dive->PLO_id/palanquees");
            $response->assertStatus(200);

            $palanquees = $dive->palanquees();
            $response->assertJsonCount($palanquees->count());
            $response->assertJsonStructure(['*' => ['id', 'plongee', 'max_profondeur', 'max_duree',
                'heure_immersion', 'heure_sortie', 'profondeur_realisee', 'duree_realisee']]);
            foreach ($palanquees as /** @var Palanquee $palanquee */ $palanquee)
                $response->assertJsonFragment(['id'=>$palanquee->PAL_id]);
        } finally {
            DB::rollBack();
        }
    }
    public function test_getPalanqueesFail() {
        DB::beginTransaction();
        try {
            $response = $this->get("/api/plongees/1234567789/palanquees");
            $response->assertStatus(404);
        } finally {
            DB::rollBack();
        }
    }
    public function test_postPalanqueesOk() {
        DB::beginTransaction();
        try {
            TestPlongeeSeeder::run(1, 10);
            /** @var Plongee $dive */
            $dive = Plongee::all()->first();
            $response = $this->postJson("/api/plongees/$dive->PLO_id/palanquees", [
                'max_profondeur'=>$dive->niveau->getMaxDepth(),
                'max_duree'=>3
            ]);
            $response->assertStatus(200);

            $response->assertJsonFragment(['max_profondeur'=>$dive->niveau->getMaxDepth(), 'max_duree'=>3,
                'plongee'=>$dive->PLO_id, "duree_realisee"=>null,"heure_immersion"=>null,"heure_sortie"=>null,
                "profondeur_realisee"=>null]);
        } finally {
            DB::rollBack();
        }
    }
    public function test_postPalanqueesFailAll() {
        DB::beginTransaction();
        try {
            TestPlongeeSeeder::run(1, 10);
            /** @var Plongee $dive */
            $dive = Plongee::all()->first();
            $response = $this->postJson("/api/plongees/$dive->PLO_id/palanquees");
            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['max_profondeur', 'max_duree']);
        } finally {
            DB::rollBack();
        }
    }
    public function test_postPalanqueesFailMaxProf() {
        DB::beginTransaction();
        try {
            TestPlongeeSeeder::run(1, 10);
            /** @var Plongee $dive */
            $dive = Plongee::all()->first();
            $response = $this->postJson("/api/plongees/$dive->PLO_id/palanquees", [
                'max_profondeur'=>$dive->niveau->getMaxDepth()+1,
                'max_duree' => 30
            ]);
            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['max_profondeur']);
        } finally {
            DB::rollBack();
        }
    }
    public function test_postPalanqueesFailMaxDuree() {
        DB::beginTransaction();
        try {
            TestPlongeeSeeder::run(1, 10);
            /** @var Plongee $dive */
            $dive = Plongee::all()->first();
            $response = $this->postJson("/api/plongees/$dive->PLO_id/palanquees", [
                'max_profondeur'=>$dive->niveau->getMaxDepth(),
                'max_duree' => -1
            ]);
            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['max_duree']);
        } finally {
            DB::rollBack();
        }
    }
}
