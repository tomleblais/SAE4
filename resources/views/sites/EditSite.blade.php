<!-- The site edition screen -->
@php
    $active = \App\Models\Lieu::find(old('id'))->LIE_active;
@endphp
<x-form heading="Modification de site{{$active?'':' inactif'}}" action="/api/lieux/{{ old('id') }}" button="Modifier"
        ariane="Accueil-Sites-Modification">
    @method("PUT")
    <x-hidden name="id"/>
    <x-input type='text' name="libelle" text="Libellé" max-length=45  />
    <x-input type='text' name="description" text="Description" max-length=100  />
    <x-slot name="otherButtons">
        @if($active)
            <input type="submit" name="deleteSite" class="w3-round-large"
                   style="padding: 16px; white-space: normal" value="Supprimer" >
        @else
            <input type="submit" name="deleteSite" class="w3-round-large"
                   style="padding: 16px; white-space: normal" value="Supprimer définitivement" >
            <input type="submit" name="restoreSite" class="w3-round-large"
                   style="padding: 16px; white-space: normal" value="Restaurer" >
        @endif
    </x-slot>
</x-form>
