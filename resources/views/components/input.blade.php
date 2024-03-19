<!-- An input component with old value, label and error message -->
<label for="{{$name}}">{{$text}} : </label>
<input name="{{$name}}" value="{{ old($name) }}"
       class="w3-input" style="margin-bottom: 12px" {{$attributes->filter(fn($v, $k) => $k!=="name" && $k!=="text")}}>
@error($name)
<div class="w3-panel w3-pale-red w3-leftbar w3-border-red">
    <p>{{ $message }}</p>
</div>
@enderror
