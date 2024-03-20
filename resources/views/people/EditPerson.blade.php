@php
    use App\Http\Controllers\PersonnesController;
@endphp

<x-form heading="Modifier une personne{{$active?'':' inactive'}}" action="/api/personnes/{{ old('id') }}"
        button="Modifier" ariane="Accueil-Personnes-Modification">
    @method("PUT")
    <x-hidden name="id"/>
    <x-input type="text" name="nom" text="Nom" maxlength=45 />
    <x-input type="text" name="prenom" text="Prénom" maxlength=45 />
    <x-input type="email" name="email" text="Adresse de Courriel" maxlength=100 />
    <div style="display: flex; justify-content: space-evenly">
        <div class="w3-center">
            <label>Directeur de section<br/>
                <input type="hidden" name="directeur_de_section" value="off"> <!-- unchecked boxes are not posted -->
                <input type="checkbox" name="directeur_de_section" {{ old('directeur_de_section')?'checked':'' }}>
            </label>
        </div>
        <div class="w3-center">
            <label>Secrétaire<br/>
                <input type="hidden" name="secretaire" value="off"> <!-- unchecked boxes are not posted -->
                <input type="checkbox" name="secretaire" {{ old('secretaire')?'checked':'' }}>
            </label>
        </div>
        <div class="w3-center">
            <label>Sécurité de surface<br/>
                <input type="hidden" name="securite_de_surface" value="off"> <!-- unchecked boxes are not posted -->
                <input type="checkbox" name="securite_de_surface" {{ old('securite_de_surface')?'checked':'' }}>
            </label>
        </div>
        <div class="w3-center">
            <label>Pilote de bateau<br/>
                <input type="hidden" name="pilote" value="off"> <!-- unchecked boxes are not posted -->
                <input type="checkbox" name="pilote" {{ old('pilote')?'checked':'' }}>
            </label>
        </div>
    </div>
    <x-slot name="otherButtons">
        @if(!$isAdherent)
            <input type="submit" name="becomeAdherent" class="w3-round-large"
                   style="padding: 16px; white-space: normal" value="Devenir Adhérent">
        @else
            <a href="/adherents/{{old('id')}}/editer" class="w3-btn w3-round-large w3-border w3-light-grey"
               style="padding: 16px; white-space: normal">Voir la page adhérent</a>
        @endif
        @if($active)
            @if(!PersonnesController::lastDirector(old('id')))
                <input type="submit" name="deletePerson" class="w3-round-large"
                   style="padding: 16px; white-space: normal" value="Supprimer">
            @endif        
        @else
            <input type="submit" name="deletePerson" class="w3-round-large"
                   style="padding: 16px; white-space: normal" value="Supprimer définitivement" >
            <input type="submit" name="restorePerson" class="w3-round-large"
                   style="padding: 16px; white-space: normal" value="Restaurer" >
        @endif
    </x-slot>
</x-form>
