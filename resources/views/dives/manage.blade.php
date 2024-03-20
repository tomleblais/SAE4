<!-- The dives management form -->
@php
    use App\Models\Personne;use App\Models\Plongee;use Illuminate\Database\Eloquent\Collection;use Illuminate\Support\Facades\DB;

    /** @var string $displayMonth */
    if ($displayMonth == 'cur') $displayMonth = now()->month;

    /** @var string $sortOrder */
    /** @var bool $sortDir */
    /** @var bool $actives */

    if ($displayMonth == 'cur') $displayMonth = now()->month;
    session()->put([
            'actives' => $actives?'true':'false',
            'mois' => $displayMonth,
            'order' => $sortOrder,
            'dir' => $sortDir?'true':'false'
        ]
    );
    
    /** @var bool $actives */
    function getSortLink(string $title, string $field, string $order, bool $act, bool $dir) : string {
        if ($field === $order){
            return "&nbsp;<a href='?actives=".($act?'true':'false')."&order=$field&dir=".($dir?'false':'true')
                    ."'>$title &nbsp ".($dir?'v':'^')."</a>";
        } else {
            return "&nbsp;<a href='?actives=".($act?'true':'false')."&order=$field&dir=false'>$title &nbsp -</a>";
        }
    }

    /** @var Personne $user */
    /** @var string $displayMonth */
    $req = Plongee::with(['lieu', 'niveau', 'moment',
        'participants'])->where('PLO_active',$actives)->orderBy('PLO_date')->orderBy('PLO_moment');
    if (! $user->isDirector() && ! $user->isSecretary())
        $req->where('PLO_directeur', $user->PER_id);
    if ($displayMonth != 'tous'){
        $req->whereMonth('PLO_date', $displayMonth);
    }

    /** @var Collection|Plongee[] $dives */
    $dives = $req->get();

    /** @var string $sortOrder */
    /** @var bool $sortDir */
    switch ($sortOrder) {
        case 'date' : if ($sortDir) $dives = $dives->reverse() ;break;
        case 'lieu' : $dives = $dives->sortBy('lieu.LIE_libelle', SORT_NATURAL, $sortDir); break;
        case 'niveau' : $dives = $dives->sortBy('niveau.NIV_niveau', SORT_NATURAL, $sortDir); break;
        case 'effectif' : $dives = $dives->sortBy(function ($v, $k)
                { return $v->participants->count(); }, SORT_NATURAL, $sortDir); break;
        case 'etat' : $dives = $dives->sortBy('PLO_etat', SORT_NATURAL, $sortDir);
    }
    $names=['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre'
            , 'décembre'];
    $usedMonths = DB::select("SELECT distinct month(PLO_date) as month
             FROM PLO_PLONGEES WHERE PLO_active = :act
             ORDER BY month", ['act'=>$actives?1:0]);
@endphp


<x-page ariane="Accueil-Gestion des plongées">
    <form method="post" class="w3-padding">@csrf
        <input type="hidden" name="order" value="{{$sortOrder}}">
        <input type="hidden" name="dir" value="{{$sortDir?'true':'false'}}">
        <input type="hidden" name="mois" value="{{$displayMonth}}">
        <h1 class="w3-center">
            <label>Gestion des plongées &nbsp;
                <input type="submit" class="w3-button w3-round-large w3-border w3-amber w3-small"
                       name="switchActive" value="{{$actives?"ACTIVES":"INACTIVES"}}"/>
                <input hidden name="actives" value="{{$actives?"false":"true"}}">
            </label>
        </h1>
    </form>
    <form method="post" class="w3-center" style="margin-bottom: 8px">@csrf
        <input type="hidden" name="actives" value="{{$actives?'true':'false'}}">
        <input type="hidden" name="order" value="{{$sortOrder}}">
        <input type="hidden" name="dir" value="{{$sortDir?'true':'false'}}">
        <label>Mois : <select name="mois" onchange="this.form.submit()">
                <option value="tous" {{ ($displayMonth=='tous')?'selected':'' }}>Tous</option>
                @foreach($usedMonths as $month)
                    <option value="{{ $month }}" {{ ($displayMonth == $month)?'selected':'' }}>
                        {{ $names[$month - 1] }}</option>
                @endforeach
            </select>
        </label>
    </form>
    @if(null != session('result'))
        <div class="w3-panel w3-pale-green w3-leftbar w3-border-green w3-border">
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
    <table class="w3-table w3-striped w3-card-4"
           style="width: 80%; margin-left: auto; margin-right: auto; padding: 8px">
        <thead class="w3-lime">
        <tr>
            <th>{!! getSortLink('Date', 'date', $sortOrder, $actives, $sortDir) !!}</th>
            <th>{!! getSortLink('Site', 'lieu', $sortOrder, $actives, $sortDir) !!}</th>
            <th class="w3-center">{!! getSortLink('Niveau requis', 'niveau', $sortOrder, $actives, $sortDir) !!}</th>
            <th class="w3-center">{!! getSortLink('Effectif', 'effectif', $sortOrder, $actives, $sortDir) !!}</th>
            <th class="w3-center">{!! getSortLink('État', 'etat', $sortOrder, $actives, $sortDir) !!}</th>
        </tr>
        </thead>
        <tbody>
        @foreach($dives as $dive)
            @php
                $dive->isPast();

                $color="";
                if ($dive->isCancelled()) {
                    $color = 'w3-blue-gray';
                } elseif ($dive->isLocked()) {
                    $color = 'w3-purple';
                } else {
                    $nbFree = $dive->nbFreeSlots();
                    if ($nbFree <= 0)
                        $color = 'w3-red';

                    elseif ($nbFree <=5)
                        $color = 'w3-yellow';
                    else
                        $color = 'w3-green';
                }
            @endphp
            <tr class="{{$color}}">
                <td><a href="/plongees/{{$dive->PLO_id}}/editer">
                        {{$dive->PLO_date->format('d/m/Y')}} ({{$dive->moment->MOM_libelle}})
                    </a></td>
                <td class="w3-tooltip">{{$dive->lieu->LIE_libelle}}
                    <span style="position:absolute;left:0;top:18px"
                          class="w3-text w3-tag w3-animate-opacity w3-round-xlarge">
                    {{$dive->lieu->LIE_description}}</span>
                </td>
                <td class="w3-center">{{$dive->niveau->NIV_code}}</td>
                <td class="w3-center"><a href="/plongees/{{$dive->PLO_id}}">
                        {{$dive->participants->count()}} / {{$dive->PLO_min_plongeurs}}-{{$dive->PLO_max_plongeurs}}
                    </a></td>
                <td class="w3-center">
                    <form method="POST" action="/api/plongees/{{$dive->PLO_id}}">@csrf @method("PUT")
                        <label><select name="etat" class="w3-input" onchange="this.form.submit()">
                                @foreach($dive->getPossibleStates() as $state)
                                    <option value="{{$state->getId()}}"
                                        {{$dive->PLO_etat == $state->getId()?'selected':''}}>
                                        {{$state->getText()}}
                                    </option>
                                @endforeach
                            </select></label>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <footer class="w3-bar w3-center w3-padding" style="display: flex; justify-content: space-around">
        <span class="w3-bar-item w3-green"> &gt; 5 places libres </span>
        <span class="w3-bar-item w3-yellow"> &le; 5 places libres </span>
        <span class="w3-bar-item w3-red"> complète </span>
        <span class="w3-bar-item w3-purple"> verrouillée </span>
        <span class="w3-bar-item w3-blue-gray"> annulée </span>
    </footer>
</x-page>
