<!-- The dive-edition form -->
<x-form heading="Modifier une plongée{{$active?'':' inactive'}}" action="/api/plongees/{{ old('id') }}"
        ariane="Accueil-Gestion des plongées-Modification"
        button="Modifier">
    @method("PUT")
    <x-hidden name="id"/>
    <x-select name="lieu" text="Lieu" :collection="$Lieu"/>
    <x-select name="bateau" text="Bateau" :collection="$Bateau"/>
    <x-input type="date" name="date" text="Date de la plongée"/>
    <x-select name="moment" text="Moment" :collection="$Moment"/>
    <x-input type="number" name="min_plongeurs" text="Nb minimum de plongeurs" min=2 />
    <x-input type="number" name="max_plongeurs" text="Nb maximum de plongeurs" min=2 />
    <x-select name="niveau" text="Niveau requis" :collection="$Niveau"/>
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