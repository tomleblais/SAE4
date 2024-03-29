<!-- The create-dive form -->

<x-form heading="Nouvelle plongée" action="/api/plongees" button="Créer" ariane="Accueil-Nouvelle plongée">
    <x-select name="lieu" text="Lieu" :collection="$Lieu" required/>
    <x-select name="bateau" text="Bateau" :collection="$Bateau" required/>
    <x-input id="date" type="date" name="date" text="Date de la plongée" required/>
    <x-select name="moment" text="Moment" :collection="$Moment" required/>
    <x-input type="number" name="min_plongeurs" text="Nb minimum de plongeurs" min=2 required/>
    <x-input type="number" name="max_plongeurs" text="Nb maximum de plongeurs" min=2 required/>
    <x-select name="niveau" text="Niveau requis" :collection="$Niveau" required/>
    <x-select name="pilote" text="Pilote" :collection="$pilotes" required/>
    <x-select name="securite_de_surface" text="Sécurité de surface" :collection="$securites" required/>
    <x-select name="directeur_de_plongee" text="Directeur de plongée" :collection="$directeurs" required/>
</x-form>

<script>
    function validateMonth() {
        var selectedDate = new Date(document.getElementById('date').value);
        var selectedMonth = selectedDate.getMonth() + 1;

        if (selectedMonth < 3 || selectedMonth > 11) {
            alert("Veuillez sélectionner un mois entre mars et novembre.");
            return false;
        }
        return true;
    }

    document.querySelector('form').addEventListener('submit', function(event) {
        if (!validateMonth()) {
            event.preventDefault();
        }
    });
</script>