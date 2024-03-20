<!-- The person update form -->
<x-form heading="Modifier un adhérent{{$active?'':' inactif'}}" action="/api/adherents/{{ old('id') }}"
        button="Modifier" ariane="Accueil-Adhérents-Modification">
    @method("PUT")
    <x-hidden name="id"/>
    <x-input type="text" name="nom" text="Nom" maxlength=45  />
    <x-input type="text" name="prenom" text="Prénom" maxlength=45  />
    <x-input type="text" name="licence" text="N° de Licence" maxlength=45  />
    <x-input type="date" name="date_certificat_medical" text="Date du certificat médical" />
    <x-input type="email" name="email" text="Adresse de Courriel" maxlength=100 />
    <x-input type="text" name="forfait" text="Forfait de plongée souscrit" maxlength=45 />
    <x-select name="niveau" text="Niveau atteint" :collection="$levels"/>
    <x-slot name="otherButtons">
        <a href="{{ url('/personnes', old('id')) }}" type="submit"
           class="w3-btn w3-round-large w3-border w3-light-grey"
           style="padding: 16px; white-space: normal">Fonctions supplémentaires</a>
        @if($active)
            <input type="submit" name="deletePerson" class="w3-round-large w3-btn w3-border w3-light-grey"
                   style="padding: 16px; white-space: normal" value="Supprimer" >
        @else
            <input type="submit" name="deletePerson" class="w3-round-large w3-btn w3-border w3-light-grey"
                   style="padding: 16px; white-space: normal" value="Supprimer définitivement" >
            <input type="submit" name="restorePerson" class="w3-round-large w3-btn w3-border w3-light-grey"
                   style="padding: 16px; white-space: normal" value="Restaurer" >
        @endif
    </x-slot>
</x-form>
