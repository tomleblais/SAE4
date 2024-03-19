<!-- The ship creation form -->
<x-form heading="Nouveau bateau" action="/api/bateaux" button="Créer" ariane="Accueil-Bateaux-Nouveau">
    <x-input type="text" name="libelle" text="Libellé" maxlength=45 required />
    <x-input type="number" name="max_personnes" text="Max de personnes à bord" min="2" required />
</x-form>
