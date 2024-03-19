<!-- The update-password screen -->
<x-form heading="Modification du mot de passe" action="/api/personnes/{{ old('id') }}" button="Changer mot de passe"
    ariane="Accueil-Modification du mot de passe">
    @method("PUT")
    <x-hidden name="id"/>
    @if(old('token') !== null)
    <x-hidden name="token"/>
    @else
    <x-input type="password" name="old_pass" text="Ancien Mot de passe (requis)" maxlength=60 required/>
    @endif
    <x-input type="password" name="pass" text="Mot de passe" maxlength=60 required/>
    <x-input type="password" name="pass_confirmation" text="Confirmation du mot de passe" maxlength=60 required/>
</x-form>
