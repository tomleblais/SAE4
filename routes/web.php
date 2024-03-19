<?php

use App\Models\Plongee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::withoutMiddleware([\App\Http\Middleware\Authenticate::class])->group(function () {

    Route::get('/', function () {
        return view('welcome');
    });

    Route::get("/hello", function () {
        return "Hello World";
    });

    Route::view('/connexion', 'connection/login')->name('login');
    Route::post('/connexion', [\App\Http\Controllers\LoginController::class, 'authenticate']);
    Route::get('/recuperation/{id}/{token}', function (Request $request, int $id, string $token) {
        return (new \App\Http\Controllers\LoginController())->recover($request, $token, \App\Models\Personne::find($id));
    });
});

Route::view("/bateaux/creer", "ships/CreateShip")->can('admin', \App\Models\Bateau::class);
Route::get("/bateaux/{id}/editer", function (\App\Models\Bateau $id) {
    if (session()->getOldInput('id') != $id->BAT_id) {
        session()->flashInput($id->toArray()); // sets old data
    }
    return view("ships/EditShip");
})->can('admin', \App\Models\Bateau::class);
Route::view("/bateaux", "/ships/ListShips")->can('admin', \App\Models\Bateau::class);
Route::post("/bateaux", function (Request $request) {
    return view("/ships/ListShips", ['actives' => $request->post('actives')!='false']);
})->can('admin', \App\Models\Bateau::class);

Route::view("/sites/creer", "sites/CreateSite")->can('admin', \App\Models\Lieu::class);
Route::get("/sites/{id}/editer", function (\App\Models\Lieu $id) {
    if (session()->getOldInput('id') != $id->LIE_id) {
        session()->flashInput($id->toArray()); // sets old data
    }
    return view("sites/EditSite");
})->can('admin', \App\Models\Lieu::class);
Route::view("/sites", "/sites/ListSites")->can('admin', \App\Models\Lieu::class);
Route::post("/sites", function (Request $request) {
    return view("/sites/ListSites", ['actives' => $request->post('actives')!='false']);
})->can('admin', \App\Models\Lieu::class);

Route::view("/adherents/creer", "people/CreateAdherent")->can('secretary', \App\Models\Personne::class);
Route::get("/adherents/changerMotDePasse", function () {
    $id = Auth::user();
    if (session()->getOldInput('id') != $id->PER_id) {
        session()->flashInput($id->toArray()); // sets old data
    }
    return view("people/changePassword");
});
Route::get("/adherents/{id}/editer", function (\App\Models\Adherent $id) {
    $id->load('personne');
    if (session()->getOldInput('licence') != $id->ADH_licence) {
        session()->flashInput($id->toArray()); // sets old data
    }
    return view("people/EditAdherent");
})->can('secretary', \App\Models\Personne::class);

Route::match(['get','post'], "/adherents", function (Request $request) {
    return view("/people/ListAdherents", [
        'actives' => $request->input('actives', session('aActive', 'true'))!='false',
        'sortOrder' => $request->input('order', session('aOrder', 'nom')),
        'sortDir' => $request->input('dir', session('aDir', 'false')) != 'false']);
})->can('secretary', \App\Models\Personne::class);

Route::view("/plongees/creer", "dives/CreateDive")->can('secretary', \App\Models\Personne::class);
Route::get("/plongees/{id}/editer", function (\App\Models\Plongee $id) {
    if (session()->getOldInput('id') != $id->PLO_id) {
        session()->flashInput($id->toArray()); // sets old data
    }
    return view("dives/EditDive");
})->can('diveDirector', 'id');

Route::match(['get','post'],"/plongees/inscriptions",function (Request $request) {
    return view("dives/registration", ['user_id' => Auth::user()->getAuthIdentifier(),
        'displayMonth' => $request->input('mois', session('mois','cur')),
        'sortOrder' => $request->input('order', session('order', 'date')),
        'sortDir' => $request->input('dir', session('dir', 'false')) != 'false']);
})->can('registerInDive', \App\Models\Personne::class);

Route::get("/plongees/{id}", function (Plongee $id) {
   return view('/dives/ShowDive', ['plongee' => $id->PLO_id]);
})->can('diveDirector', 'id');

Route::match(['get','post'],"/plongees",function (Request $request) {
    return view("dives/manage", ['user' => Auth::user(),
        'actives' => $request->input('actives', session('actives', 'true')) != 'false',
        'displayMonth' => $request->input('mois', session('mois','cur')),
        'sortOrder' => $request->input('order', session('order', 'date')),
        'sortDir' => $request->input('dir', session('dir', 'false')) != 'false']);
})->can('manageDives', \App\Models\Plongee::class)
    ->middleware('cache.headers:private;no_cache;no_store'); //Reload on back-pressed

Route::match(['get','post'], "/personnes", function (Request $request) {
    return view("/people/ListPeople", [
        'actives' => $request->input('actives', session('pActive', 'true'))!='false',
        'sortOrder' => $request->input('order', session('pOrder', 'nom')),
        'sortDir' => $request->input('dir', session('pDir', 'false')) != 'false'
        ]);
})->can('secretary', \App\Models\Personne::class);
Route::view("/personnes/creer", "people/CreatePerson")->can('secretary', \App\Models\Personne::class);
Route::get("/personnes/{id}", function (\App\Models\Personne $id) {
    //if (session()->getOldInput('id') != $id->PER_id) {
        $id->load("autorisations");
        session()->flashInput($id->toArray()); // sets old data
    //}
    return view("people/EditPerson");
})->can('secretary', \App\Models\Personne::class);
Route::get('/accueil', function (Request $request) {
    return view('home/dashboard');
});
