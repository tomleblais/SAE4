<!-- The dive-detail screen -->
@php
    use App\Models\Palanquee;
    use App\Models\Adherent;
    use App\Models\Plongee;use Illuminate\Support\Facades\DB;
    assert(isset($plongee));
    /** @var Plongee $dive */
    $dive = Plongee::with('niveau', 'bateau', 'moment', 'lieu', 'etat',
    'palanquees.members.adherent', 'participants.niveau')->find($plongee);
@endphp
<x-page ariane="Accueil-Gestion des plongées-Configuration">
    <h1 class="w3-center"> Plongée du {{$dive->PLO_date}} {{$dive->moment->MOM_libelle}} ({{$dive->lieu->LIE_libelle}}
        )</h1>
    <div class="w3-card-4" style="width: 80%; margin-left: auto; margin-right: auto; padding: 8px"
         ondragenter="allowDrop(event, -1)" ondragover="allowDrop(event, -1)" ondrop="drop(event, -1)">
        <div class="w3-panel">
            <h3>Description :</h3>
            {{$dive->lieu->LIE_description}}
        </div>
        <div class="w3-panel">
            <h3>Embarcation :</h3>
            {{$dive->bateau->BAT_libelle}} (max. {{$dive->bateau->BAT_max_personnes}} personnes)
        </div>
        <div class="w3-panel">
            <h3>Niveau requis :</h3>
            {{$dive->niveau->NIV_libelle}} ({{$dive->niveau->NIV_code}})
        </div>
        <div class="w3-panel">
            <h3>Participants :</h3>
            Entre {{$dive->PLO_min_plongeurs}} et {{$dive->PLO_max_plongeurs}} plongeurs.
            <ul class="w3-ul w3-row">
                @php
                    $participants = $dive->participants()->with('personne', 'niveau')->orderBy("ADH_niveau")->get();
                @endphp
                @foreach($participants as /**@var Adherent $adherent*/ $adherent)
                    @if (empty(DB::select(
                        "SELECT INC_id FROM PLO_INCLUT
                         JOIN PLO_PALANQUEES ON INC_palanquee=PAL_id
                         WHERE PAL_plongee=:plongee AND INC_adherent=:adherent",
                         ['plongee'=>$dive->PLO_id, 'adherent'=>$adherent->ADH_id])))
                        <li draggable="{{$dive->isLocked()?'false':'true'}}"
                            ondragstart="drag(event, -1, {{$adherent->ADH_id}})" class="w3-col s12 m6 l3"
                            style="cursor: grab">
                    @else
                        <li style="cursor: not-allowed" class="w3-col s12 m6 l3">
                            @endif
                            {{$adherent->personne->PER_nom}} {{$adherent->personne->PER_prenom}}
                            ({{$adherent->niveau->NIV_code}})
                        </li>
                        @endforeach
            </ul>
        </div>
    </div>
    @foreach($dive->palanquees as /**@var Palanquee $pal*/ $pal)
        @if($loop->odd) <!-- odd iteration - open row -->
            <div class="w3-row-padding w3-stretch">
        @endif
        @include('palanquees/EditPalanquee')
        @if($loop->even) <!-- even iteration - close row -->
            </div>
        @endif
    @endforeach
    @if($dive->palanquees->count() < 4)
        <div class="w3-half w3-card-2">
            @include('palanquees/CreatePalanquee')
        </div>
    @endif
    @if($dive->palanquees->count() % 2 == 1)
        </div> <!-- close the unclosed row when odd number of panacles -->
    @endif
</x-page>
