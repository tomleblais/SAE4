<?php

namespace App\Policies;

use App\Models\Adherent;
use App\Models\Bateau;
use App\Models\Inclut;
use App\Models\Palanquee;
use App\Models\Personne;
use App\Models\Plongee;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use function Symfony\Component\Translation\t;

class InclutPolicy extends Rights
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param Personne $person
     * @return bool
     */
    private function isAdmin(Personne $person): bool
    {
        $rights = $this->getAuthorizations("admin Inclut", $person);
        return ($rights['AUT_directeur_section'] == 1)
            || ($rights['AUT_secretaire'] == 1);
    }

}
