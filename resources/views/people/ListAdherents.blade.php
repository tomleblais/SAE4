<!-- The adherents-list screen -->
@php
    use App\Models\Adherent;

    /** @var bool $actives */
    /** @var bool $sortDir */
    /** @var string $sortOrder */

   session()->put([
            'aActives' => $actives?'true':'false',
            'aOrder' => $sortOrder,
            'aDir' => $sortDir?'true':'false'
            ]);

    $data = Adherent::with(['personne','niveau'])
        ->whereHas('personne', function ($query) use ($actives) {
            $query->where('PER_active', $actives);})->get()
        ->sortBy(['personne.PER_nom','personne.PER_prenom']);

    function getSortLink(string $title, string $field, string $order, bool $act, bool $dir) : string {
        if ($field === $order){
            return "&nbsp;<a href='?actives=".($act?'true':'false')."&order=$field&dir=".($dir?'false':'true')
                    ."'>$title &nbsp ".($dir?'v':'^')."</a>";
        } else {
            return "&nbsp;<a href='?actives=".($act?'true':'false')."&order=$field&dir=false'>$title &nbsp -</a>";
        }
    }

    switch ($sortOrder) {
        case 'nom' : $data = $data->sortBy('personne.PER_nom', SORT_NATURAL, $sortDir); break;
        case 'prenom' : $data = $data->sortBy('personne.PER_prenom', SORT_NATURAL, $sortDir); break;
        case 'email' : $data = $data->sortBy('personne.PER_email', SORT_NATURAL, $sortDir); break;
        case 'niveau' : $data = $data->sortBy('ADH_niveau', SORT_NATURAL, $sortDir); break;
        case 'forfait' : $data = $data->sortBy('ADH_forfait', SORT_NATURAL, $sortDir); break;
    }
@endphp
<x-page ariane="Accueil-Adhérents">
    <form method="post" class="w3-padding">
        <h1 class="w3-center">
            @csrf
            <label>Liste des adhérents &nbsp;
                <input type="submit" class="w3-button w3-round-large w3-border w3-amber w3-small"
                                       name="switchActive" value="{{$actives?"ACTIFS":"INACTIFS"}}"/>
                <input hidden name="actives" value="{{$actives?"false":"true"}}">
            </label>
        </h1>
    </form>
    <a href="/adherents/creer" class="w3-button w3-border w3-round-xlarge">Insérer un nouvel adhérent...</a>
    <table class="w3-table-all" style="table-layout: auto">
        <thead>
        <tr>
            <th>{!! getSortLink('Nom', 'nom', $sortOrder, $actives, $sortDir) !!}</th>
            <th>{!! getSortLink('Prénom', 'prenom', $sortOrder, $actives, $sortDir) !!}</th>
            <th>{!! getSortLink('Email', 'email', $sortOrder, $actives, $sortDir) !!}</th>
            <th class="w3-center" style="width: 7em">{!! getSortLink('Niveau', 'niveau', $sortOrder, $actives, $sortDir) !!}</th>
            <th class="w3-center" style="width: 7em">{!! getSortLink('Forfait', 'forfait', $sortOrder, $actives, $sortDir) !!}</th>
        </tr>
        </thead>
        <tbody>
        @foreach($data as $pers)
            <tr class="w3-hover-green" style="cursor: cell">
                <td><a href="{{url("/adherents/".$pers->ADH_id.'/editer')}}">{{$pers->personne->PER_nom}}</a></td>
                <td>{{$pers->personne->PER_prenom}}</td>
                <td>{{$pers->personne->PER_email}}</td>
                <td class="w3-center">{{$pers->niveau->NIV_code}}</td>
                <td class="w3-center">{{$pers->ADH_forfait}}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</x-page>
