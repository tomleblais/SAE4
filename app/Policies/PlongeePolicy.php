<?php

namespace App\Policies;

use App\Models\Participe;
use App\Models\Personne;
use App\Models\Plongee;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class PlongeePolicy extends Rights
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param Personne $personne
     * @return Response|bool
     */
    public function admin(Personne $personne)
    {
        $rights = $this->getAuthorizations("admin Bateau", $personne);
        return ($rights['AUT_directeur_section'] == 1)
            || ($rights['AUT_secretaire'] == 1);
    }

    public function manageDives(Personne $personne): bool
    {
        return $personne->isDirector() || $personne->isSecretary() ||
            Plongee::where('PLO_directeur', $personne->PER_id)->exists();
    }

    public function diveDirector(Personne $person, Plongee $dive): bool
    {
        return $person->PER_id == $dive->directeur->ADH_id || $person->isSecretary() || $person->isDirector();
    }

    public function diveMember(Personne $person, Plongee $dive): bool
    {
        return Participe::all()->where('PAR_plongee', $dive->PLO_id)
            ->where('PAR_adherent', $person->PER_id)->count() > 0;
    }
}
