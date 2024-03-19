<!-- The sites list screen -->
@php
    use App\Models\Lieu;

    if (!isset($actives))
        $actives = true;
@endphp
<x-page ariane="Accueil-Sites">
    <form method="post" class="w3-padding">@csrf
        <h1 class="w3-center">
            <label>Gestion des sites &nbsp;
                <input type="submit" class="w3-button w3-round-large w3-border w3-amber w3-small"
                       name="switchActive" value="{{$actives?"ACTIFS":"INACTIFS"}}"/>
                <input hidden name="actives" value="{{$actives?"false":"true"}}">
            </label>
        </h1>
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
            <th>Nom</th>
            <th>Description</th>
        </tr>
        </thead>
        <tbody>
        @foreach(Lieu::where('LIE_active', $actives)->get() as $site)
            <tr>
                <td><a href="/sites/{{$site->LIE_id}}/editer"> {{$site->LIE_libelle}} </a></td>
                <td>{{$site->LIE_description}}</td>
            </tr>
        @endforeach
        <tr>
            <td colspan="2">
                <a href="/sites/creer" class="w3-button w3-border w3-round-xlarge">Nouveau site...</a>
            </td>
        </tr>
        </tbody>
    </table>
</x-page>
