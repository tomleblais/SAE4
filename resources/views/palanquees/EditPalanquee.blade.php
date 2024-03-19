<!-- The edit-panacle form -->
@php
    use App\Models\Etat;
    use App\Models\Plongee;
    use App\Models\Palanquee;

    /** @var Plongee $dive */
    /** @var Palanquee $pal */

    if (! $pal->isNotComplete())
        $color = 'w3-green';
    elseif ($pal->isOk())
        $color = 'w3-lime';
    else
        $color = 'w3-red';
@endphp
<div class="w3-half w3-card-2" ondragenter="allowDrop(event, {{$pal->PAL_id}})"
     ondragover="allowDrop(event, {{$pal->PAL_id}})" ondrop="drop(event, {{$pal->PAL_id}})">
    <form action="/api/palanquees/{{$pal->PAL_id}}" class="{{$color}}" method="POST">
        @csrf @method("DELETE")
        <h1>
            <button type="submit" class="w3-btn" style="width: auto">
                <i class="material-icons">delete</i>
            </button>
            Palanquée {{$loop->iteration}}:
        </h1>
    </form>
    @if($dive->PLO_etat==Etat::$CREATED)
        @include("/palanquees/created/form")
    @elseif($dive->isLocked())
        @include("/palanquees/locked/form")
    @else
        @include("/palanquees/parametrized/form")
    @endif
    <div class="w3-container">
        <h4>Membres :</h4>
        <ul class="w3-ul w3-row">
            @foreach($pal->members as /**@var Inclut $inclus*/ $adherent)
                <li draggable="{{$dive->isLocked()?'false':'true'}}"
                    ondragstart="drag(event, {{$pal->PAL_id}}, {{$adherent->INC_id}})"
                    style="cursor: grab" class="w3-col s12 m12 l6">
                    {{$adherent->adherent->personne->PER_nom}} {{$adherent->adherent->personne->PER_prenom}}
                    ({{$adherent->adherent->niveau->NIV_code}})
                </li>
            @endforeach
            <li>&nbsp;</li>
        </ul>
    </div>
</div>
