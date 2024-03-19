<!-- The new-person form -->
<x-form heading="Insérer une personne" action="/api/personnes" button="Insérer" ariane="Accueil-Personnes-Nouvelle">
    @method("POST")
    <x-hidden name="id"/>
    <x-input type="text" name="nom" text="Nom" maxlength=45  />
    <x-input type="text" name="prenom" text="Prénom" maxlength=45  />
    <x-input type="password" name="pass" text="Mot de passe" maxlength=60 />
    <x-input type="password" name="pass_confirmation" text="Confirmation du mot de passe" maxlength=60  />
    <x-input type="email" name="email" text="Adresse de Courriel" maxlength=100 />
    <div style="display: flex; justify-content: space-evenly">
        <div class="w3-center">
            <label>Directeur de section<br/>
                <input type="hidden" name="directeur_de_section" value="off"> <!-- unchecked boxes are not posted -->
                <input type="checkbox" name="directeur_de_section" {{ old('directeur_de_section')?'checked':'' }}>
            </label>
        </div>
        <div  class="w3-center">
            <label>Secrétaire<br/>
                <input type="hidden" name="secretaire" value="off"> <!-- unchecked boxes are not posted -->
                <input type="checkbox" name="secretaire" {{ old('secretaire')?'checked':'' }}>
            </label>
        </div>
        <div  class="w3-center">
            <label>Sécurité de surface<br/>
                <input type="hidden" name="securite_de_surface" value="off"> <!-- unchecked boxes are not posted -->
                <input type="checkbox" name="securite_de_surface" {{ old('securite_de_surface')?'checked':'' }}>
            </label>
        </div>
        <div  class="w3-center">
            <label>Pilote de bateau<br/>
                <input type="hidden" name="pilote" value="off"> <!-- unchecked boxes are not posted -->
                <input type="checkbox" name="pilote" {{ old('pilote')?'checked':'' }}>
            </label>
        </div>
    </div>
</x-form>
