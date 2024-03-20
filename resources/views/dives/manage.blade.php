<!-- The dives management form -->
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
                    <option value="{{ $month->month }}" {{ ($displayMonth == $month->month)?'selected':'' }}>
                        {{ $names[intval($month->month)-1] }}</option>
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
            <th>{!! $instance::getSortLink('Date', 'date', $sortOrder, $actives, $sortDir) !!}</th>
            <th>{!! $instance::getSortLink('Site', 'lieu', $sortOrder, $actives, $sortDir) !!}</th>
            <th class="w3-center">{!! $instance::getSortLink('Niveau requis', 'niveau', $sortOrder, $actives, $sortDir) !!}</th>
            <th class="w3-center">{!! $instance::getSortLink('Effectif', 'effectif', $sortOrder, $actives, $sortDir) !!}</th>
            <th class="w3-center">{!! $instance::getSortLink('État', 'etat', $sortOrder, $actives, $sortDir) !!}</th>
        </tr>
        </thead>
        <tbody>
        @foreach($dives as $dive)
            <tr class="{{$instance->whatTheColor($dive)}}">
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
