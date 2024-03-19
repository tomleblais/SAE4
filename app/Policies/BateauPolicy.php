<?php

namespace App\Policies;

use App\Models\Adherent;
use App\Models\Bateau;
use App\Models\Personne;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Log;

class BateauPolicy extends Rights
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param Personne $person
     * @return bool
     */
    public function admin(Personne $person): bool
    {
        return $person->isDirector() || $person->isSecretary();
    }

}
