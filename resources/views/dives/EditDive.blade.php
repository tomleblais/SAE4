<!-- The dive-edition form -->
@php
    use App\Models\Adherent;use App\Models\Lieu;
    use App\Models\Bateau;
    use App\Models\Moment;
    use App\Models\Niveau;use App\Models\Personne;use App\Models\Plongee;

    $active = Plongee::find(old('id'))->PLO_active;
    $pilotes = Personne::whereHas('autorisations', function ($query){
        $query->where('AUT_pilote', true);
    })->get();
    $securites = Personne::whereHas('autorisations', function ($query){
        $query->where('AUT_securite_surface', true);
    })->get();
    $directeurs = Adherent::with('personne')->whereHas('niveau', function ($query){
        $query->where('NIV_directeur', true);
    })->get();

    // Récupérer l'ID de la plongée à éditer
    $plongeeId = old('id');

    // Vérifier le statut de la plongée
    $plongee = Plongee::find($plongeeId);
    $status = $plongee->PLO_etat;

    // Si le statut est "Validée" (3), rediriger vers une page d'erreur
    if ($status == 3) {
        $errorMessage = 'Vous ne pouvez pas éditer une plongée validée.';
    }

    @endphp
    @if(isset($errorMessage))
        <div class="alert alert-danger" role="alert">
            {{ $errorMessage }}
        </div>
    @else
<x-form heading="Modifier une plongée{{$active?'':' inactive'}}" action="/api/plongees/{{ old('id') }}"
        ariane="Accueil-Gestion des plongées-Modification"
        button="Modifier">
    @method("PUT")
    <x-hidden name="id"/>
    <x-select name="lieu" text="Lieu" :collection="Lieu::all()"/>
    <x-select name="bateau" text="Bateau" :collection="Bateau::all()"/>
    <x-input type="date" name="date" text="Date de la plongée"/>
    <x-select name="moment" text="Moment" :collection="Moment::all()"/>
    <x-input type="number" name="min_plongeurs" text="Nb minimum de plongeurs" min=2 />
    <x-input type="number" name="max_plongeurs" text="Nb maximum de plongeurs" min=2 />
    <x-select name="niveau" text="Niveau requis" :collection="Niveau::all()"/>
    <x-select name="pilote" text="Pilote" :collection="$pilotes"/>
    <x-select name="securite_de_surface" text="Sécurité de surface" :collection="$securites"/>
    <x-select name="directeur_de_plongee" text="Directeur de plongée" :collection="$directeurs"/>
    <x-slot name="otherButtons">
        @if($active)
            <input type="submit" name="deleteDive" class="w3-round-large"
                   style="padding: 16px; white-space: normal" value="Supprimer">
        @else
            <input type="submit" name="deleteDive" class="w3-round-large"
                   style="padding: 16px; white-space: normal" value="Supprimer définitivement">
            <input type="submit" name="restoreDive" class="w3-round-large"
                   style="padding: 16px; white-space: normal" value="Restaurer">
        @endif
    </x-slot>
</x-form>
@endif