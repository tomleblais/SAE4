<!-- A select-from-list component with old value -->
<label for="{{$name}}">{{$text}} :
    <select name="{{$name}}" class="w3-input" style="margin-bottom: 12px" {{$attributes->filter(fn($v, $k) => $k!=="name" && $k!=="text" && $k!=='collection')}}>
        @if(null === old($name))
            <option value="" selected disabled>{{old($name)}}</option>
        @endif
        @foreach($collection as $item)
            <option
                value="{{$item->getId()}}" {{ (old($name)==$item->getId()) ? 'selected':'' }}>{{$item->getText()}}
            </option>
        @endforeach
    </select>
</label>
