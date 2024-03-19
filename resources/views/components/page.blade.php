<!-- The page frame with title and navigation bar -->
@php use Illuminate\Support\Facades\Config; @endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" style="height: 100%">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="/css/app.css">
    <script src="/js/app.js"></script>
    <title> {{ $title ?? 'Inscription aux plongées' }} </title>
</head>
<body onload="onLoad()" {{$attributes}}>
<header class="w3-cyan w3-display-container w3-padding-24">
    <div>
    @if(Auth::user() != null)
        <span class="w3-display-topleft w3-margin-left">{{ Auth::user()->PER_nom }} {{ Auth::user()->PER_prenom }}</span>
        <span class="w3-display-topright w3-margin-right"><a href="{{url('/connexion')}}">Déconnexion</a></span>
    @endif
    </div>
    <div class="w3-display-bottomleft w3-margin-left" style="margin-bottom: 2px">
        <em>
        @foreach(explode('-',$ariane) as $name)
            &nbsp; &gt; &nbsp;
            <a href="{{Config::get("locations.$name")}}" class="w3-amber">{{$name}}</a>
        @endforeach
        </em>
    </div>
</header>
{{ $slot }}
</body>
</html>
