@php
    use app\Models\Personne;

    /** @var bool $actives */
    /** @var bool $sortDir */
    /** @var string $sortOrder */

    $data = Personne::with('autorisations')->where('PER_active', $actives)->orderBy('PER_nom')
            ->orderBy('PER_prenom')->get();

    session()->put([
            'pActives' => $actives?'true':'false',
            'pOrder' => $sortOrder,
            'pDir' => $sortDir?'true':'false'
            ]);

    function getSortLink(string $title, string $field, string $order, bool $act, bool $dir) : string {
        if ($field === $order){
            return "&nbsp;<a href='?actives=".($act?'true':'false')."&order=$field&dir=".($dir?'false':'true')
                    ."'>$title &nbsp ".($dir?'v':'^')."</a>";
        } else {
            return "&nbsp;<a href='?actives=".($act?'true':'false')."&order=$field&dir=false'>$title &nbsp -</a>";
        }
    }

    switch ($sortOrder) {
        case 'nom' : $data = $data->sortBy('PER_nom', SORT_NATURAL, $sortDir); break;
        case 'prenom' : $data = $data->sortBy('PER_prenom', SORT_NATURAL, $sortDir); break;
        case 'email' : $data = $data->sortBy('PER_email', SORT_NATURAL, $sortDir); break;
        case 'directeur' : $data = $data->sortBy('autorisations.AUT_directeur_section', SORT_NATURAL, $sortDir); break;
        case 'secretaire' : $data = $data->sortBy('autorisations.AUT_secretaire', SORT_NATURAL, $sortDir); break;
        case 'securite' : $data = $data->sortBy('autorisations.AUT_securite_surface', SORT_NATURAL, $sortDir); break;
        case 'pilote' : $data = $data->sortBy('autorisations.AUT_pilote', SORT_NATURAL, $sortDir); break;
        case 'adherent' : $data = $data->sortBy(function ($v, $k)
                {return $v->isAdherent();}, SORT_NATURAL, $sortDir); break;
    }
@endphp
<x-page ariane="Accueil-Personnes">
    <form method="post" class="w3-padding">
        <h1 class="w3-center">
            @csrf
            <label>Liste des personnes &nbsp;
                <input type="submit" class="w3-button w3-round-large w3-border w3-amber w3-small"
                                   name="switchActive" value="{{$actives?"ACTIVES":"INACTIVES"}}"/>
                <input hidden name="actives" value="{{$actives?"false":"true"}}">
            </label>
        </h1>
    </form>
    <a href="/personnes/creer" class="w3-button w3-border w3-round-xlarge">Insérer une nouvelle personne...</a>
    <table class="w3-table-all" style="table-layout: auto">
        <thead>
        <tr>
            <th>{!! getSortLink('Nom', 'nom', $sortOrder, $actives, $sortDir) !!}</th>
            <th>{!! getSortLink('Prénom', 'prenom', $sortOrder, $actives, $sortDir) !!}</th>
            <th>{!! getSortLink('Email', 'email', $sortOrder, $actives, $sortDir) !!}</th>
            <th class="w3-center" style="width: 1em">{!! getSortLink('Directeur', 'directeur', $sortOrder, $actives, $sortDir) !!}</th>
            <th class="w3-center" style="width: 1em">{!! getSortLink('Secrétaire', 'secretaire', $sortOrder, $actives, $sortDir) !!}</th>
            <th class="w3-center" style="width: 1em">{!! getSortLink('Sécurité', 'securite', $sortOrder, $actives, $sortDir) !!}</th>
            <th class="w3-center" style="width: 1em">{!! getSortLink('Pilote', 'pilote', $sortOrder, $actives, $sortDir) !!}</th>
            <th class="w3-center" style="width: 1em">{!! getSortLink('Adhérent', 'adherent', $sortOrder, $actives, $sortDir) !!}</th>
        </tr>
        </thead>
        <tbody>
        @foreach($data as $pers)
            <tr class="w3-hover-green" style="cursor: cell">
                <td><a href="{{url("/personnes", $pers->PER_id)}}">{{$pers->PER_nom}}</a></td>
                <td>{{$pers->PER_prenom}}</td>
                <td>{{$pers->PER_email}}</td>
                <td class="w3-center">{{$pers->isDirector()?"✔":'✘'}}</td>
                <td class="w3-center">{{$pers->isSecretary()?"✔":'✘'}}</td>
                <td class="w3-center">{{$pers->isSurfaceSecurity()?"✔":'✘'}}</td>
                <td class="w3-center">{{$pers->isPilot()?"✔":'✘'}}</td>
                <td class="w3-center">{{$pers->isAdherent()?"✔":'✘'}}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</x-page>
