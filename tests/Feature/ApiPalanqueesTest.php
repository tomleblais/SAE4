<?php

namespace Tests\Feature;

use App\Models\Adherent;
use App\Models\Inclut;
use App\Models\Palanquee;
use App\Models\Plongee;
use App\Models\Participe;
use App\Models\Personne;
use Database\Seeders\TestPlongeeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use function PHPUnit\Framework\assertEquals;

class ApiPalanqueesTest extends TestCase
{
    use RefreshDatabase;

    public function test_getPalanqueesEmpty()
    {
        $response = $this->getJson('/api/palanquees');
        $response->assertStatus(200);
        $response->assertExactJson( [] );
    }
    public function test_getPalanqueesOk()
    {
        DB::beginTransaction();
        try {
            TestPlongeeSeeder::run(3,10);
            $response = $this->getJson('/api/palanquees');
            $response->assertStatus(200);
            $palanquees = Palanquee::all();
            $response->assertJsonCount($palanquees->count());
            $response->assertJsonStructure(['*' => ['id', 'plongee', 'max_profondeur', 'max_duree', 'heure_immersion',
                'heure_sortie', 'profondeur_realisee', 'duree_realisee']]);
            foreach ($palanquees as /** @var Palanquee $p */$p) {
                $response->assertJsonFragment(['id'=>$p->PAL_id]);
            }
        } finally {
            DB::rollBack();
        }
    }
    public function test_getPalanqueesOkWithDetails()
    {
        DB::beginTransaction();
        try {
            TestPlongeeSeeder::run(3,10);
            $response = $this->getJson('/api/palanquees/details');
            $response->assertStatus(200);
            $palanquees = Palanquee::all();
            $response->assertJsonCount($palanquees->count());
            $response->assertJsonStructure(['*' => ['id', 'plongee', 'max_profondeur', 'max_duree', 'heure_immersion',
                'heure_sortie', 'profondeur_realisee', 'duree_realisee', 'plongee_niveau', 'plongee_etat']]);
            foreach ($palanquees as /** @var Palanquee $p */$p) {
                $response->assertJsonFragment(['id'=>$p->PAL_id]);
            }
        } finally {
            DB::rollBack();
        }
    }
    public function test_getOnePalanqueeOk()
    {
        DB::beginTransaction();
        try {
            TestPlongeeSeeder::run(3,10, null, 4);
            $palanquee = Palanquee::first();
            $response = $this->getJson("/api/palanquees/$palanquee->PAL_id");
            $response->assertStatus(200);
            $response->assertExactJson(['id'=>$palanquee->PAL_id, 'plongee' => $palanquee->PAL_plongee,
                'max_profondeur'=> $palanquee->PAL_max_prof, 'max_duree' => $palanquee->PAL_max_duree,
                'heure_immersion' => $palanquee->PAL_heure_immersion, 'heure_sortie' => $palanquee->PAL_heure_sortie,
                'profondeur_realisee' => $palanquee->PAL_prof_realisee,
                'duree_realisee' => $palanquee->PAL_duree_realisee]);
        } finally {
            DB::rollBack();
        }
    }
    public function test_getOnePalanqueeOkWithDetails()
    {
        DB::beginTransaction();
        try {
            TestPlongeeSeeder::run(3,10, null, 4);
            $palanquee = Palanquee::first();
            $response = $this->getJson("/api/palanquees/$palanquee->PAL_id/details");
            $response->assertStatus(200);
            $response->assertExactJson(['id'=>$palanquee->PAL_id, 'plongee' => $palanquee->PAL_plongee,
                'max_profondeur'=> $palanquee->PAL_max_prof, 'max_duree' => $palanquee->PAL_max_duree,
                'heure_immersion' => $palanquee->PAL_heure_immersion, 'heure_sortie' => $palanquee->PAL_heure_sortie,
                'profondeur_realisee' => $palanquee->PAL_prof_realisee,
                'duree_realisee' => $palanquee->PAL_duree_realisee,
                'plongee_niveau' => $palanquee->plongee->niveau->NIV_code,
                'plongee_etat' => $palanquee->plongee->etat->ETA_libelle
            ]);
        } finally {
            DB::rollBack();
        }
    }
    public function test_deletePalanqueeOk() {
        DB::beginTransaction();
        try {
            TestPlongeeSeeder::run(3,10, null, 4);
            $palanquee = Palanquee::first();
            $response = $this->deleteJson("/api/palanquees/$palanquee->PAL_id");
            $response->assertStatus(200);

            $this->assertModelMissing($palanquee);
        } finally {
            DB::rollBack();
        }
    }
    public function test_getPalanqueeMembersOk()
    {
        DB::beginTransaction();
        try {
            TestPlongeeSeeder::run(3,10, null, 4);
            $palanquee = Palanquee::first();
            $response = $this->getJson("/api/palanquees/$palanquee->PAL_id/membres");
            $response->assertStatus(200);
            $members = $palanquee->members;
            $response->assertJsonCount($members->count());
            $response->assertJsonStructure(['*' => ['id', 'adherent', 'palanquee']]);
            foreach ($members as /** @var Inclut $included */$included) {
                $response->assertJsonFragment(['id' => $included->INC_id,
                    "adherent" => $included->INC_adherent,
                    "palanquee" => $included->INC_palanquee
                ]);
            }
        } finally {
            DB::rollBack();
        }
    }
    public function test_getPalanqueeMembersOkWithDetails()
    {
        DB::beginTransaction();
        try {
            TestPlongeeSeeder::run(3,10, null, 4);
            $palanquee = Palanquee::first();
            $response = $this->getJson("/api/palanquees/$palanquee->PAL_id/membres/details");
            $response->assertStatus(200);
            $members = $palanquee->members()->with("adherent.niveau")->get();
            $response->assertJsonCount($members->count());
            $response->assertJsonStructure(['*' => ['id', 'adherent', 'palanquee']]);
            foreach ($members as /** @var Inclut $included */$included) {
                $response->assertJsonFragment(['id' => $included->INC_id,
                    "adherent" => $included->INC_adherent,
                    "palanquee" => $included->INC_palanquee,
                    "adherent_nom" => $included->adherent->personne->PER_nom,
                    "adherent_prenom" => $included->adherent->personne->PER_prenom,
                    "adherent_niveau" => $included->adherent->niveau->NIV_code]);
            }
        } finally {
            DB::rollBack();
        }
    }
    public function test_postMemberOk()
    {
        DB::beginTransaction();
        try {
            TestPlongeeSeeder::run(1,10, null, 4);
            $dive = Plongee::first();
            /** @var Palanquee $palanquee */
            $palanquee = $dive->palanquees->first();
            /** @var Personne $person */
            $person = Personne::factory()->active()->has(Adherent::factory()->state(['ADH_niveau'=>$dive->PLO_niveau]))->create();
            Participe::create(['PAR_plongee'=>$dive->PLO_id, 'PAR_adherent'=>$person->PER_id]);
            $response = $this->postJson("/api/palanquees/$palanquee->PAL_id/membres", [ 'adherent'=>$person->PER_id ]);
            $response->assertStatus(200);
            $response->assertJsonFragment(['adherent'=>$person->PER_id, 'palanquee'=>$palanquee->PAL_id]);
            $member = Inclut::find($response->json('id'));
            self::assertNotNull($member);
            assertEquals($palanquee->PAL_id, $member->INC_palanquee);
            assertEquals($person->PER_id, $member->INC_adherent);
        } finally {
            DB::rollBack();
        }
    }
    public function test_postMemberFailNoAdherent()
    {
        DB::beginTransaction();
        try {
            TestPlongeeSeeder::run(1,10, null, 4);
            $dive = Plongee::first();
            /** @var Palanquee $palanquee */
            $palanquee = $dive->palanquees->first();
            $response = $this->postJson("/api/palanquees/$palanquee->PAL_id/membres");
            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['adherent']);
        } finally {
            DB::rollBack();
        }
    }
    public function test_postMemberFailNoParticipant()
    {
        DB::beginTransaction();
        try {
            TestPlongeeSeeder::run(1,10, null, 4);
            $dive = Plongee::first();
            /** @var Palanquee $palanquee */
            $palanquee = $dive->palanquees->first();
            /** @var Personne $person */
            $person = Personne::factory()->active()->has(Adherent::factory()->state(['ADH_niveau'=>$dive->PLO_niveau]))->create();
            $response = $this->postJson("/api/palanquees/$palanquee->PAL_id/membres", [ 'adherent'=>$person->PER_id ]);
            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['adherent']);
        } finally {
            DB::rollBack();
        }
    }
    public function test_postMemberFailNoLevel()
    {
        DB::beginTransaction();
        try {
            TestPlongeeSeeder::run(1, 10, null, 4, 1);
            $dive = Plongee::first();
            /** @var Palanquee $palanquee */
            $palanquee = $dive->palanquees->first();
            /** @var Personne $person */
            $person = Personne::factory()->active()->has(Adherent::factory()->state(['ADH_niveau' => $dive->PLO_niveau]))->create();
            Participe::create(['PAR_plongee' => $dive->PLO_id, 'PAR_adherent' => $person->PER_id]);
            $person->adherent->ADH_niveau = $dive->PLO_niveau-1;
            $person->adherent->save();
//            print_r($person);
//            print_r($dive);
            $response = $this->postJson("/api/palanquees/$palanquee->PAL_id/membres", ['adherent' => $person->PER_id]);
            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['adherent']);
        } finally {
            DB::rollBack();
        }
    }
    public function test_postMemberFailDuplicate()
    {
        DB::beginTransaction();
        try {
            TestPlongeeSeeder::run(1,10, null, 4);
            $dive = Plongee::first();
            /** @var Palanquee $palanquee */
            $palanquee = $dive->palanquees->first();
            /** @var Palanquee $newPal */
            $newPal = $dive->palanquees()->create(['PAL_max_prof'=>5, 'PAL_max_duree'=>10]);
            /** @var Personne $person */
            $person = $palanquee->members()->first();
            $response = $this->postJson("/api/palanquees/$newPal->PAL_id/membres", [ 'adherent'=>$person->PER_id ]);
            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['adherent']);
        } finally {
            DB::rollBack();
        }
}
    public function test_postParticipantFailAllNoData()
    {
        DB::beginTransaction();
        try {
            $response = $this->postJson("/api/participants/");
            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['plongee', 'adherent']);
        } finally {
            DB::rollBack();
        }
    }
    public function test_postParticipantFailAllBadData()
    {
        DB::beginTransaction();
        try {
            $response = $this->postJson("/api/participants/", [
                'adherent'=>0,
                'plongee'=>0
            ]);
            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['plongee', 'adherent']);
        } finally {
            DB::rollBack();
        }
    }
    public function test_postParticipantFailAdherentLevel()
    {
        DB::beginTransaction();
        try {
            TestPlongeeSeeder::run(1,10);
            $dive = Plongee::first();
            if ($dive->PLO_niveau <= 2) { //ensure a diver can be too weak
                $dive->PLO_niveau = 3;
                $dive->save();
            }
            /** @var Personne $person */
            $person = Personne::factory()->active()->has(Adherent::factory()->state(['ADH_niveau'=>1]))->create();
            $response = $this->postJson("/api/participants/", [
                'adherent'=>$person->PER_id,
                'plongee'=>$dive->PLO_id
            ]);
            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['adherent']);
        } finally {
            DB::rollBack();
        }
    }

    public function test_getOneMemberOk()
    {
        DB::beginTransaction();
        try {
            TestPlongeeSeeder::run(3,10, null, 4);
            $palanquee = Palanquee::first();
            /** @var Inclut $member */
            $member = Inclut::where('INC_palanquee', $palanquee->PAL_id)->first();
            $response = $this->getJson("/api/palanquees/membres/$member->INC_id");
            $response->assertStatus(200);
            $response->assertExactJson(
                ["id" => $member->INC_id, "adherent" => $member->INC_adherent,
                    'palanquee' => $member->INC_palanquee]
            );
        } finally {
            DB::rollBack();
        }
    }
    public function test_getOneMemberOkWithDetails()
    {
        DB::beginTransaction();
        try {
            TestPlongeeSeeder::run(3,10, null, 4);
            $palanquee = Palanquee::first();
            /** @var Inclut $member */
            $member = $palanquee->members()->first();
            $response = $this->getJson("/api/palanquees/membres/$member->INC_id/details");
            $response->assertStatus(200);
            $response->assertExactJson(
                ["id" => $member->INC_id, "adherent" => $member->INC_adherent,
                    'palanquee' => $member->INC_palanquee,
                    "adherent_nom" => $member->adherent->personne->PER_nom,
                    "adherent_prenom" => $member->adherent->personne->PER_prenom,
                    "adherent_niveau" => $member->adherent->niveau->NIV_code,
                    "palanquee_plongee" => $member->palanquee->PAL_plongee,
                    "palanquee_max_prof" => $member->palanquee->PAL_max_prof,
                    "palanquee_niveau" => $member->palanquee->plongee->niveau->NIV_code
                ]);
        } finally {
            DB::rollBack();
        }
    }
    public function test_deleteMemberOk()
    {
        DB::beginTransaction();
        try {
            TestPlongeeSeeder::run(3,10, null, 4);
            $palanquee = Palanquee::first();
            /** @var Inclut $member */
            $member = Inclut::where('INC_palanquee', $palanquee->PAL_id)->first();
            $response = $this->deleteJson("/api/palanquees/membres/$member->INC_id");
            $response->assertStatus(200);
            $this->assertModelMissing($member);
        } finally {
            DB::rollBack();
        }
    }

    public function test_putMemberOk()
    {
        DB::beginTransaction();
        try {
            TestPlongeeSeeder::run(1,10, null, 4);
            $dive = Plongee::first();
            /** @var Palanquee $palanquee */
            $palanquee = $dive->palanquees->first();
            /** @var Inclut $member */
            $member = Inclut::where('INC_palanquee', $palanquee->PAL_id)->first();
            $diverID = $member->INC_adherent;
            /** @var Palanquee $newPalanquee */
            $newPalanquee = $dive->palanquees()->create(['PAL_max_prof'=>3, 'PAL_max_duree'=>12]);
            $response = $this->putJson("/api/palanquees/membres/$member->INC_id",
                [ 'palanquee'=>$newPalanquee->PAL_id ]);
            $response->assertStatus(200);
            $response->assertJsonFragment(['adherent'=>$diverID, 'palanquee'=>$newPalanquee->PAL_id]);
            $member = Inclut::find($response->json('id'));
            self::assertNotNull($member);
            assertEquals($newPalanquee->PAL_id, $member->INC_palanquee);
            assertEquals($diverID, $member->INC_adherent);
        } finally {
            DB::rollBack();
        }
    }
    public function test_putMemberFailNoPalanquee()
    {
        DB::beginTransaction();
        try {
            TestPlongeeSeeder::run(1,10, null, 4);
            $dive = Plongee::first();
            /** @var Palanquee $palanquee */
            $palanquee = $dive->palanquees->first();
            /** @var Inclut $member */
            $member = Inclut::where('INC_palanquee', $palanquee->PAL_id)->first();
            $response = $this->putJson("/api/palanquees/membres/$member->INC_id");
            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['palanquee']);
        } finally {
            DB::rollBack();
        }
    }
    public function test_putMemberFailBadData()
    {
        DB::beginTransaction();
        try {
            TestPlongeeSeeder::run(1,10, null, 4);
            $dive = Plongee::first();
            /** @var Palanquee $palanquee */
            $palanquee = $dive->palanquees->first();
            /** @var Inclut $member */
            $member = Inclut::where('INC_palanquee', $palanquee->PAL_id)->first();
            $response = $this->putJson("/api/palanquees/membres/$member->INC_id", [ 'palanquee'=>0 ]);
            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['palanquee']);
        } finally {
            DB::rollBack();
        }
    }
}
