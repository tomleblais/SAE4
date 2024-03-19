<?php

namespace App\Policies;

use App\Models\Personne;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Rights
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    protected function getAuthorizations(string $which, Personne $personne): array
    {
        $res = DB::select("SELECT * FROM PLO_AUTORISATIONS WHERE AUT_personne=:id", ['id' => $personne->PER_id]);
        Log::debug("Checking rights to $which for {$personne->PER_id}:", $res);
        return array_merge([
            'AUT_directeur_section'=>0,
            'AUT_securite_surface'=>0,
            'AUT_pilote'=>0,
            'AUT_secretaire'=>0,
        ], $res);
    }

}
