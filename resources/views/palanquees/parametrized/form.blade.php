<!-- The parametrized dive update form -->
<form action="/api/palanquees/{{$pal->PAL_id}}" method="POST">
    @csrf @method("PUT")
    <div class="w3-row">
        <div class="w3-half w3-padding-small">
            <h4>Profondeur max. : {{$pal->PAL_max_prof}} m.</h4>
        </div>
        <div class="w3-half w3-padding-small">
            <h4><label> P. réalisée. :
                <input name="profondeur_realisee" value = '{{$pal->PAL_prof_realisee}}'
                       class="w3-right-align" style="max-width: 4em" type="number" min="0"> m.
            </label></h4>
            @error('profondeur_realisee', 'palanquee'.$pal->PAL_id)
            <div class="w3-panel w3-pale-red w3-leftbar w3-border-red">
                <p>{{ $message }}</p>
            </div>
            @enderror
        </div>
    </div>
    <div class="w3-row">
        <div class="w3-half w3-padding-small">
            <h4>Durée max. : {{$pal->PAL_max_duree}} min.</h4>
        </div>
        <div class="w3-half w3-padding-small">
            <h4><label> D. réalisée. :
                <input name="duree_realisee" value = '{{$pal->PAL_duree_realisee}}'
                       class="w3-right-align" style="max-width: 4em" type="number" min="0"> min.
            </label></h4>
            @error('duree_realisee', 'palanquee'.$pal->PAL_id)
            <div class="w3-panel w3-pale-red w3-leftbar w3-border-red">
                <p>{{ $message }}</p>
            </div>
            @enderror
        </div>
    </div>
    <div class="w3-row">
        <div class="w3-half w3-padding-small">
            <h4><label> H. Immersion :
                <input name="heure_immersion" value = '{{$pal->getImmersion()}}'
                       class="w3-right-align" type="time" min="0">
            </label></h4>
            @error('heure_immersion', 'palanquee'.$pal->PAL_id)
            <div class="w3-panel w3-pale-red w3-leftbar w3-border-red">
                <p>{{ $message }}</p>
            </div>
            @enderror
        </div>
        <div class="w3-half w3-padding-small">
            <h4><label> Heure Sortie :
                <input name="heure_sortie" value = '{{$pal->getSortie()}}'
                       class="w3-right-align"  type="time" min="0">
            </label></h4>
            @error('heure_sortie', 'palanquee'.$pal->PAL_id)
            <div class="w3-panel w3-pale-red w3-leftbar w3-border-red">
                <p>{{ $message }}</p>
            </div>
            @enderror
        </div>
    </div>
    <div class="w3-center">
        <input type="submit" value="Valider" class="w3-round-large w3-btn w3-border w3-light-grey">
    </div>
</form>
