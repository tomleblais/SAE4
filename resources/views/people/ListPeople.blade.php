@php
    use App\Http\Controllers\PlongeesController;
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
            <th>{!! PlongeesController::getSortLink('Nom', 'nom', $sortOrder, $actives, $sortDir) !!}</th>
            <th>{!! PlongeesController::getSortLink('Prénom', 'prenom', $sortOrder, $actives, $sortDir) !!}</th>
            <th>{!! PlongeesController::getSortLink('Email', 'email', $sortOrder, $actives, $sortDir) !!}</th>
            <th class="w3-center" style="width: 1em">{!! PlongeesController::getSortLink('Directeur', 'directeur', $sortOrder, $actives, $sortDir) !!}</th>
            <th class="w3-center" style="width: 1em">{!! PlongeesController::getSortLink('Secrétaire', 'secretaire', $sortOrder, $actives, $sortDir) !!}</th>
            <th class="w3-center" style="width: 1em">{!! PlongeesController::getSortLink('Sécurité', 'securite', $sortOrder, $actives, $sortDir) !!}</th>
            <th class="w3-center" style="width: 1em">{!! PlongeesController::getSortLink('Pilote', 'pilote', $sortOrder, $actives, $sortDir) !!}</th>
            <th class="w3-center" style="width: 1em">{!! PlongeesController::getSortLink('Adhérent', 'adherent', $sortOrder, $actives, $sortDir) !!}</th>
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
