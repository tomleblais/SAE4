<!-- The site creation form -->
<x-form title="Créer un site" heading="Nouveau site" action="/api/lieux" method="POST" button="Créer"
        ariane="Accueil-Sites-Nouveau">
    <x-input type='text' name="libelle" text="Libellé" max-length=45 required />
    <x-input type='text' name="description" text="Description" max-length=100 required />
</x-form>

