<!-- The ship edition form -->
@php
  $active = \App\Models\Bateau::find(old('id'))->BAT_active;
@endphp
<x-form heading="Modification de bateau{{$active?'':' inactif'}}" action="/api/bateaux/{{ old('id') }}"
        button="Modifier" ariane="Accueil-Bateaux-Modification">
    @method("PUT")
    <x-hidden name="id"/>
    <x-input type="text" name="libelle" text="Libellé" max-length=45  />
    <x-input type="number" name="max_personnes" text="Max de personnes à bord" min="2" />
    <x-slot name="otherButtons">
        @if($active)
            <input type="submit" name="deleteShip" class="w3-round-large"
                   style="padding: 16px; white-space: normal" value="Supprimer" >
        @else
            <input type="submit" name="deleteShip" class="w3-round-large"
                   style="padding: 16px; white-space: normal" value="Supprimer définitivement" >
            <input type="submit" name="restoreShip" class="w3-round-large"
                   style="padding: 16px; white-space: normal" value="Restaurer" >
        @endif
    </x-slot>
</x-form>
