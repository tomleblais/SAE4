<!-- The login form -->
@php
\Illuminate\Support\Facades\Auth::logout()
@endphp
<x-form  heading="Connexion" action="/connexion" button="Se connecter" ariane="Connexion">
    <x-input type="email" name="email" text="Addresse de Courriel" maxlength=100 required/>
    <x-input type="password" name="pass" text="Mot de passe" maxlength=60 />
    <p class="w3-right-align" style="margin-top: -0.5em">
        <input type="submit" name="forgotten" value="Mot de passe oublié" class="submitLink"/></p>
</x-form>
