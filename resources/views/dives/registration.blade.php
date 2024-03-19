<!-- The show-dives screen with register/unregister checkbox when possible -->
@php
    use App\Models\Adherent;use App\Models\Plongee;use Illuminate\Support\Facades\DB;

    session()->put([
            'displayMonth' => $displayMonth,
            'sortOrder' => $sortOrder,
            'sortDir' => $sortDir
            ]);

    function getSortLink(string $title, string $field, string $order, bool $dir) : string {
        if ($field === $order){
            return "&nbsp;<a href='?order=$field&dir=".($dir?'false':'true')
                    ."'>$title &nbsp ".($dir?'v':'^')."</a>";
        } else {
            return "&nbsp;<a href='?order=$field&dir=false'>$title &nbsp -</a>";
        }
    }

    /** @var int $user_id */
    $user = Adherent::find($user_id);
    $dives = Plongee::with(['lieu', 'niveau', 'moment',
        'participants'=> function($query) use ($user_id) { $query->where("PAR_adherent", $user_id); }
        ])->where('PLO_active','1')->orderBy('PLO_date')->orderBy('PLO_moment')->whereHas('niveau',
            function ($query) use($user) {
                $query->where('NIV_niveau', '<=', $user->niveau->NIV_niveau);
            });
    if ($displayMonth != 'tous')
        $dives = $dives->whereMonth('PLO_date', $displayMonth)->get();
    else
        $dives = $dives->get();
    switch ($sortOrder) {
        case 'date' : if ($sortDir == 'desc') $dives = $dives->reverse() ;break;
        case 'lieu' : $dives = $dives->sortBy('lieu.LIE_libelle', SORT_NATURAL, $sortDir); break;
        case 'niveau' : $dives = $dives->sortBy('niveau.NIV_niveau', SORT_NATURAL, $sortDir); break;
        case 'participe' : $dives = $dives->sortBy(function ($v,$k) {
            return $v->participants->count();
        }, SORT_NATURAL, $sortDir);
    }
    $names=['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre'
            , 'décembre'];
    $usedMonths = DB::select("SELECT distinct month(PLO_date) as month
             FROM PLO_PLONGEES JOIN PLO_NIVEAUX ON PLO_niveau=NIV_id
             WHERE NIV_niveau <= :niv AND PLO_active = 1
             ORDER BY month", ['niv'=>$user->niveau->NIV_niveau]);
@endphp
<x-page ariane="Accueil-Inscriptions aux plongées">
    <h1 class="w3-center">Inscriptions aux plongées</h1>
    <form class="w3-center" style="margin-bottom: 8px" method="POST">
        @csrf
        <input type="hidden" name="order" value="{{$sortOrder}}">
        <input type="hidden" name="dir" value="{{$sortDir?'true':'false'}}">
        <label>Mois : <select name="mois" onchange="this.form.submit()">
                <option value="tous" {{ ($displayMonth=='tous')?'selected':'' }}>Tous</option>
                @foreach($usedMonths as $month)
                    <option value="{{ $month->month }}" {{ ($displayMonth == $month->month)?'selected':'' }}>
                        {{ $names[intval($month->month)-1] }}</option>
                @endforeach
            </select>
        </label>
    </form>
    <table class="w3-table w3-striped w3-card-4"
           style="width: 80%; margin-left: auto; margin-right: auto; padding: 8px">
        <thead class="w3-lime">
        <tr>
            <th>{!! getSortLink('Date', 'date', $sortOrder, $sortDir) !!}</th>
            <th>{!! getSortLink('Site', 'lieu', $sortOrder, $sortDir) !!}</th>
            <th>{!! getSortLink('Niveau requis', 'niveau', $sortOrder, $sortDir) !!}</th>
            <th>{!! getSortLink('Participe', 'participe', $sortOrder, $sortDir) !!}</th>
        </tr>
        </thead>
        <tbody>
        @foreach($dives as $dive)
            @if($user->niveau->NIV_niveau >= $dive->niveau->NIV_niveau)
                <tr @class([
        'w3-blue-gray' => $dive->isCancelled(),
        'w3-purple' => !$dive->isCancelled() && $dive->isLocked(),
        'w3-red' => !($dive->isCancelled() || $dive->isLocked()) && $dive->nbFreeSlots()<=0,
        'w3-yellow' => !($dive->isCancelled() || $dive->isLocked()) && $dive->nbFreeSlots()>0 && $dive->nbFreeSlots()<=5,
        'w3-green' => !($dive->isCancelled() || $dive->isLocked()) && $dive->nbFreeSlots()>5,
])>
                    <td>{{$dive->PLO_date->format('d/m/Y')}} {{$dive->moment->MOM_libelle}}</td>
                    <td class="w3-tooltip">{{$dive->lieu->LIE_libelle}}
                        <span style="position:absolute;left:0;top:18px"
                              class="w3-text w3-tag w3-animate-opacity w3-round-xlarge">
                        {{$dive->lieu->LIE_description}}</span>
                    </td>
                    <td>{{$dive->niveau->NIV_code}}</td>
                    <td>
                        <form
                        @if ($dive->participants->isEmpty())
                            @include('dives.registration.register')
                        @else
                            @include('dives.registration.unregister')
                        @endif
                        <!-- {{$dive->participants->toJson()}} -->
                    </td>

                </tr>
            @endif
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
