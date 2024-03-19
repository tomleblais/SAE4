<!-- The new-adherent form -->
<x-form title="Créer un adhérent" heading="Nouvel adhérent" action="/api/adherents" button="Créer"
 ariane="Accueil-Adhérents-Nouveau">
    <x-input type="text" name="nom" text="Nom" maxlength=45 required />
    <x-input type="text" name="prenom" text="Prénom" maxlength=45 required />
    <x-input type="text" name="licence" text="N° de Licence" maxlength=45 required />
    <x-input type="date" name="date_certificat_medical" text="Date du certificat médical" />
    <x-input type="password" name="pass" text="Mot de passe" maxlength=60 required />
    <x-input type="password" name="pass_confirmation" text="Confirmation du mot de passe" maxlength=60 required />
    <x-input type="email" name="email" text="Addresse de Courriel" maxlength=100 required/>
    <x-input type="text" name="forfait" text="Forfait de plongée souscrit" maxlength=45 />
    <x-select name="niveau" text="Niveau atteint" :collection="Niveau::all()"/>
</x-form>
