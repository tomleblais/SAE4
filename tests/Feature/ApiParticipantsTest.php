<?php

namespace Tests\Feature;

use App\Models\Adherent;
use App\Models\Plongee;
use App\Models\Participe;
use App\Models\Personne;
use Database\Seeders\TestPlongeeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ApiParticipantsTest extends TestCase
{
    use RefreshDatabase;

    public function test_getParticipantsEmpty()
    {
        $response = $this->getJson('/api/participants');
        $response->assertStatus(200);
        $response->assertExactJson( [] );
    }
    public function test_getParticipantsOk()
    {
        DB::beginTransaction();
        try {
            TestPlongeeSeeder::run(3,10);
            $response = $this->getJson('/api/participants');
            $response->assertStatus(200);
            $participants = Participe::all();
            $response->assertJsonCount($participants->count());
            $response->assertJsonStructure(['*' => ['id', 'adherent', 'plongee']]);
            foreach ($participants as /** @var Participe $p */$p) {
                $response->assertJsonFragment(['id'=>$p->PAR_id]);
            }
        } finally {
            DB::rollBack();
        }
    }
    public function test_getParticipantsOkWithDetails()
    {
        DB::beginTransaction();
        try {
            TestPlongeeSeeder::run(3,10);
            $response = $this->getJson('/api/participants/details');
            $response->assertStatus(200);
            $participants = Participe::all();
            self::assertNotEmpty($participants);
            $response->assertJsonCount($participants->count());
            $response->assertJsonStructure(['*' => ['id', 'adherent', 'plongee', 'adherent_nom', 'adherent_prenom',
                'adherent_niveau', 'plongee_date', 'plongee_moment', 'plongee_niveau']]);
            foreach ($participants as /** @var Participe $p */$p) {
                $response->assertJsonFragment(['id'=>$p->PAR_id]);
            }
        } finally {
            DB::rollBack();
        }
    }
    public function test_getOneParticipantOk()
    {
        DB::beginTransaction();
        try {
            TestPlongeeSeeder::run(3,10);
            $participant = Participe::first();
            $response = $this->getJson("/api/participants/$participant->PAR_id");
            $response->assertStatus(200);
            $response->assertExactJson(['id'=>$participant->PAR_id, 'adherent'=>$participant->PAR_adherent,
                'plongee'=>$participant->PAR_plongee]);
        } finally {
            DB::rollBack();
        }
    }
    public function test_getOneParticipantOkWithDetails()
    {
        DB::beginTransaction();
        try {
            TestPlongeeSeeder::run(3,10);
            $participant = Participe::first();
            $response = $this->getJson("/api/participants/$participant->PAR_id/details");
            $response->assertStatus(200);
            $response->assertExactJson(['id'=>$participant->PAR_id, 'adherent'=>$participant->PAR_adherent,
                'plongee'=>$participant->PAR_plongee, "adherent_nom" => $participant->adherent->personne->PER_nom,
                "adherent_prenom" => $participant->adherent->personne->PER_prenom,
                "adherent_niveau" => $participant->adherent->niveau->NIV_code,
                "plongee_date" => $participant->plongee->PLO_date,
                "plongee_moment" => $participant->plongee->moment->MOM_libelle,
                "plongee_niveau" => $participant->plongee->niveau->NIV_code]);
        } finally {
            DB::rollBack();
        }
    }
    public function test_deleteParticipantOk() {
        DB::beginTransaction();
        try {
            TestPlongeeSeeder::run(3,10);
            $participant = Participe::first();
            $response = $this->deleteJson("/api/participants/$participant->PAR_id");
            $response->assertStatus(200);

            $this->assertModelMissing($participant);
        } finally {
            DB::rollBack();
        }
    }

    public function test_postParticipantOk()
    {
        DB::beginTransaction();
        try {
            TestPlongeeSeeder::run(1,10);
            $dive = Plongee::first();
            /** @var Personne $person */
            $person = Personne::factory()->active()->has(Adherent::factory()->state(['ADH_niveau'=>$dive->PLO_niveau]))->create();
            $response = $this->postJson("/api/participants/", [
                'adherent'=>$person->PER_id,
                'plongee'=>$dive->PLO_id
            ]);
            $response->assertStatus(200);
            $response->assertJsonFragment(['adherent'=>$person->PER_id, 'plongee'=>$dive->PLO_id]);
        } finally {
            DB::rollBack();
        }
    }
    public function test_postParticipantFailAllNoDate()
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

}
