<?php

namespace Tests\Feature;

use App\Models\Adherent;
use App\Models\Autorisations;
use App\Models\Personne;
use App\Models\Plongee;
use Database\Seeders\TestPersonneSeeder;
use Database\Seeders\TestPlongeeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Throwable;

class SeedersTest extends TestCase
{
  use RefreshDatabase;

    /**
     * A basic test example.
     *
     * @return void
     * @throws Throwable
     */
    public function test_seeder()
    {
        /*
        $response = $this->get('/');

        $response->assertStatus(200);
        DB::beginTransaction();
        Personne::factory()->count(10)->create();
        DB::rollBack();
        */
        DB::beginTransaction();
        try {
            TestPersonneSeeder::run(); // Adds 4 admins and 1 top-level diver and 50 adherents
            TestPlongeeSeeder::run(); // Adds 10 dives
            $this->assertDatabaseCount(Autorisations::class, 5);
            $this->assertDatabaseCount(Personne::class, 56);
            $this->assertDatabaseCount(Adherent::class, 51);
            $this->assertDatabaseCount(Plongee::class, 10);
        } catch (Throwable $exception) {
            print_r($exception->getMessage());
            print_r($exception->getTraceAsString());
            throw $exception;
        } finally {
            DB::rollBack();
        }
    }
}
