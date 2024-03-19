<!-- The mail message to recover password -->
<p>
Bonjour {{$user->PER_prenom}} {{$user->PER_nom}}.
</p>
<p>
Vous avez demandé à récupérer votre mot de passe pour le site d'inscription aux plongées.
Si ce n'est pas vous, ignorez simplement ce courriel.
</p>

<p>
Pour modifier votre mot de passe, cliquez ici :
<A href='{{url("/recuperation/".$user->PER_id."/".$user->PER_remember_token)}}'>
  Connexion de secours
</A>
</p>

