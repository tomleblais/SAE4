<!-- The welcome screen -->
@php use App\Models\Personne;use App\Models\Plongee; @endphp
<x-page style="height:100%" ariane="Accueil">
    <div
        style="display: flex; flex-direction: column; justify-content: space-evenly; height: 100%; align-items: center">
        <h1 class="w3-center">Accueil</h1>
        <a href="/adherents/changerMotDePasse" class="w3-round-large w3-btn w3-border w3-light-blue">
            Modifier son mot de passe
        </a>
        @canany('secretary', Personne::class)
            <a href="/personnes" class="w3-round-large w3-btn w3-border w3-light-grey">
                Gérer les personnes
            </a>
            <a href="/adherents" class="w3-round-large w3-btn w3-border w3-light-grey">
                Gérer les adhérents
            </a>
            <a href="/bateaux" class="w3-round-large w3-btn w3-border w3-light-grey">
                Gérer les bateaux
            </a>
            <a href="/sites" class="w3-round-large w3-btn w3-border w3-light-grey">
                Gérer les sites
            </a>
            <a href="/plongees/creer" class="w3-round-large w3-btn w3-border w3-light-grey">
                Créer une plongées
            </a>
        @endcanany
        @can('manageDives', Plongee::class)
            <a href="/plongees" class="w3-round-large w3-btn w3-border w3-light-green">
                Gérer les plongées
            </a>
        @endcan
        @can('registerInDive', Personne::class)
            <a href="/plongees/inscriptions" class="w3-round-large w3-btn w3-border w3-yellow">
                Inscriptions aux plongées
            </a>
        @endcan
        <h1>&nbsp;</h1> {{-- final spacing --}}
    </div>
</x-page>
