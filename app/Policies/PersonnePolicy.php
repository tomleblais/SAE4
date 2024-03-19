<?php

namespace App\Policies;

use App\Models\Personne;
use Illuminate\Auth\Access\HandlesAuthorization;

class PersonnePolicy extends Rights
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param Personne $personne
     * @return bool
     */
    public function admin(Personne $personne): bool
    {
        $rights = $this->getAuthorizations("admin Personne", $personne);
        return ($rights['AUT_directeur_section'] == 1)
            || ($rights['AUT_secretaire'] == 1);
    }

    public function registerInDive(Personne $personne) : bool {
        return $personne->isAdherent();
    }

    public function secretary(Personne $person): bool
    {
        return $person->isSecretary() || $person->isDirector();
    }

    public function director(Personne $person): bool
    {
        return $person->isDirector();
    }
}
