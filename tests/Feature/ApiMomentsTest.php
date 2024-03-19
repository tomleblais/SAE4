<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiMomentsTest extends TestCase
{
    public function test_getMoments()
    {
        $response = $this->getJson('/api/moments');
        $response->assertStatus(200);
        $response->assertExactJson( [
            ["id"=>1,"libelle"=>"Matin"],
            ["id"=>2,"libelle"=>"Après-midi"],
            ["id"=>3,"libelle"=>"Soirée"]
        ]);
    }
    public function test_getOneMomentOk()
    {
        $response = $this->getJson('/api/moments/2');
        $response->assertStatus(200);
        $response->assertExactJson(
            ["id"=>2,"libelle"=>"Après-midi"]
        );
    }
    public function test_getOneMomentFail()
    {
        $response = $this->getJson('/api/moments/0');
        $response->assertStatus(404);
    }
}
