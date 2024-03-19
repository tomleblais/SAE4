<!-- The register-in-dive check-box -->
<form action="/api/participants" method="POST">
    <input type="hidden" name="adherent" value="{{$user_id}}">
    <input type="hidden" name="plongee" value="{{$dive->PLO_id}}">
    <label>
        <input type="checkbox" name="register" onchange="submit()" class="w3-hover-green" {{($dive->isCancelled()||$dive->isLocked()||$dive->nbFreeSlots()<1)?'disabled':''}}>
    </label>
</form>
