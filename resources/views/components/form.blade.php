<!-- A form page with old values, error messages and submit button -->
<x-page :ariane="$ariane">
@if(null != session('result'))
    <div class="w3-panel w3-pale-green w3-leftbar w3-border-green w3-border">
        <p>{{ session('result') }}</p>
    </div>
@endif
@if ($errors->any())
    <div class="alert alert-danger" style="display: none">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

    <h1 class="w3-center"> {{ $heading }} </h1>
    <form action="{{ $action }}" method="{{ $method ?? 'POST' }}" class="w3-card-4" style="width: 80%; margin-left: auto; margin-right: auto; padding: 8px">
        @csrf
        <input style="overflow: visible !important; height: 0 !important; width: 0 !important; margin: 0 !important; border: 0 !important; padding: 0 !important; display: block !important;" type="submit" value="default action"/>
        {{ $slot }}
        <div class="w3-padding-16" style="display: flex; justify-content: space-evenly">
            <input type="submit" value="{{ $button }}" class="w3-round-large w3-btn w3-border w3-light-grey" style="padding: 16px">
            @isset($otherButtons)
            {{ $otherButtons }}
            @endif
        </div>
    </form>
</x-page>
