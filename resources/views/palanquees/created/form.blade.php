<!-- The created dive update form -->
<form action="/api/palanquees/{{$pal->PAL_id}}" method="POST">
    @csrf @method("PUT")
    <div class="w3-container">
        <h4><label> Profondeur max. :
            <input name="max_profondeur" value = '{{$pal->PAL_max_prof}}' class="w3-right-align"
                   style="max-width: 4em" type="number" min="0" onchange="this.form.submit()"> m.
        </label></h4>
        @error('max_profondeur', 'palanquee'.$pal->PAL_id)
        <div class="w3-panel w3-pale-red w3-leftbar w3-border-red">
            <p>{{ $message }}</p>
        </div>
        @enderror
    </div>
    <div class="w3-container">
        <h4><label> Durée max. :
            <input name="max_duree" value = '{{$pal->PAL_max_duree}}' class="w3-right-align"
                   style="max-width: 4em" type="number" min="0" onchange="this.form.submit()"> min.
        </label></h4>
        @error('max_duree', 'palanquee'.$pal->PAL_id)
        <div class="w3-panel w3-pale-red w3-leftbar w3-border-red">
            <p>{{ $message }}</p>
        </div>
        @enderror
    </div>
</form>
