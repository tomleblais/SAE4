<!-- The create-panacle form -->
@if(null != session('result'))
    <div class="w3-panel w3-pale-green w3-leftbar w3-border-green w3-border vanish" >
        <p>{{ session('result') }}</p>
    </div>
@endif
@if ($errors->any())
    <div class="alert alert-danger" style="display: none">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@php
@endphp

<h1 class="w3-center w3-lime"> Nouvelle palanquée </h1>
<form action="/api/plongees/{{$plongee}}/palanquees" method="POST" style="padding: 8px">
    @csrf
    <x-input type="number" name="max_profondeur" text="Profondeur maximale" min="0" max="{{ $dive->lieu->LIE_prof_max }}" required placeholder="{{ $dive->lieu->LIE_prof_max }}" />

    <x-input type="number" name="max_duree" text="Durée maximale de plongée" min="0" required />
    <div style="text-align: center;" class="w3-padding-16">
        <input type="submit" value="Créer" class="w3-round-large" style="padding: 16px">
    </div>
</form>
