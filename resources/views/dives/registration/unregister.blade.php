<!-- The leave-dive check-box -->
<form action="/api/plongees/{{$dive->PLO_id}}/participants/{{$user_id}}" method="POST">
    @method("DELETE")
    <label>
        <input type="checkbox" name="register" onchange="submit()" class="w3-hover-red" checked {{$dive->isLocked()?'disabled':''}}>
    </label>
</form>
