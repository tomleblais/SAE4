<!-- The locked dive read-only form -->
<div class="w3-row">
    <div class="w3-half w3-padding-small">
        <h4>Profondeur max. : {{$pal->PAL_max_prof}} m.</h4>
    </div>
    <div class="w3-half w3-padding-small">
        <h4>P. réalisée. : {{$pal->PAL_prof_realisee}} m.</h4>
    </div>
</div>
<div class="w3-row">
    <div class="w3-half w3-padding-small">
        <h4>Durée max. : {{$pal->PAL_max_duree}} min.</h4>
    </div>
    <div class="w3-half w3-padding-small">
        <h4>D. réalisée. : {{$pal->PAL_duree_realisee}}' min.</h4>
    </div>
</div>
<div class="w3-row">
    <div class="w3-half w3-padding-small">
        <h4>H. Immersion : {{$pal->getImmersion()}} </h4>
    </div>
    <div class="w3-half w3-padding-small">
        <h4>Heure Sortie : {{$pal->getSortie()}}</h4>
    </div>
</div>
