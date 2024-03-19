<?php

namespace Tests\Feature;

use App\Models\Adherent;
use App\Models\Personne;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ApiNiveauxTest extends TestCase
{
    use RefreshDatabase;

    public function test_getNiveaux()
    {
        $response = $this->getJson('/api/niveaux');
        $response->assertStatus(200);
        $response->assertJson(fn (AssertableJson $json) =>
        $json->has(15)
            ->first(fn (AssertableJson $json0) =>
            $json0->where('id', 0)
                ->where('code', 'PB')
                ->where('libelle', 'Plongeur Bronze')
                ->where('profondeur_si_encadre', 6)
                ->where('profondeur_si_autonome', 0)
                ->where('niveau', 0)
                ->where('guide_de_palanquee', false)
                ->where('directeur_de_plongee', false)
            )
        );
        $response->assertJson(fn (AssertableJson $json) =>
        $json->where('14.id', 14)
            ->where('14.code', 'E4')
            ->where('14.libelle', 'Encadrant Niveau 4')
            ->where('14.profondeur_si_encadre', 0)
            ->where('14.profondeur_si_autonome', 60)
            ->where('14.niveau', 51)
            ->where('14.guide_de_palanquee', true)
            ->where('14.directeur_de_plongee', true)
        );
        $response->assertJson(fn (AssertableJson $json) =>
        $json->where('13.id', 13)
            ->where('13.guide_de_palanquee', true)
            ->where('13.directeur_de_plongee', false)
        );
    }
    public function test_getOneNiveauOk()
    {
        $response = $this->getJson('/api/niveaux/5');
        $response->assertStatus(200);
        $response->assertJson(fn (AssertableJson $json) =>
            $json->where('id', 5)
                ->where('code', 'PE-12')
                ->where('libelle', 'Plongeur Encadré 12m')
                ->where('profondeur_si_encadre', 12)
                ->where('profondeur_si_autonome', 0)
                ->where('niveau', 11)
                ->where('guide_de_palanquee', false)
                ->where('directeur_de_plongee', false)
        );
    }
    public function test_getOneNiveauFail()
    {
        $response = $this->getJson('/api/niveaux/15');
        $response->assertStatus(404);
    }
    public function test_getAdherentsForOneNiveauFail()
    {
        $response = $this->getJson('/api/niveaux/15/adherents');
        $response->assertStatus(404);
    }
    public function test_getAdherentsForOneNiveauEmpty()
    {
        $response = $this->getJson('/api/niveaux/5/adherents');
        $response->assertStatus(200);
        $response->assertJson(fn (AssertableJson $json) =>
            self::assertEmpty($json->toArray()));
    }
    public function test_getAdherentsForOneNiveauNonEmpty()
    {
        DB::beginTransaction();
        try {
            $pers = Personne::factory()->count(3)->has(Adherent::factory()->state(['ADH_niveau' => 5]))->create();
            $response = $this->getJson('/api/niveaux/5/adherents');
            $response->assertStatus(200);
            $response->assertJsonCount(3);
            foreach ($pers as /** @var Personne $p */$p)
                $response->assertJsonFragment(['id'=>$p->PER_id]);
        } finally {
            DB::rollBack();
        }
    }
}
