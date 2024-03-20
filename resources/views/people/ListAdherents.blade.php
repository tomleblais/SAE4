<!-- The adherents-list screen -->
@php
    use App\Http\Controllers\PlongeesController;
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
            <th>{!! PlongeesController::getSortLink('Nom', 'nom', $sortOrder, $actives, $sortDir) !!}</th>
            <th>{!! PlongeesController::getSortLink('Prénom', 'prenom', $sortOrder, $actives, $sortDir) !!}</th>
            <th>{!! PlongeesController::getSortLink('Email', 'email', $sortOrder, $actives, $sortDir) !!}</th>
            <th class="w3-center" style="width: 7em">{!! PlongeesController::getSortLink('Niveau', 'niveau', $sortOrder, $actives, $sortDir) !!}</th>
            <th class="w3-center" style="width: 7em">{!! PlongeesController::getSortLink('Forfait', 'forfait', $sortOrder, $actives, $sortDir) !!}</th>
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
