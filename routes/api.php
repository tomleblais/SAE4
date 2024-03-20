<?php

use App\Http\Controllers\AdherentsController;
use App\Http\Controllers\BateauxController;
use App\Http\Controllers\InclutController;
use App\Http\Controllers\LieuxController;
use App\Http\Controllers\MomentsController;
use App\Http\Controllers\NiveauxController;
use App\Http\Controllers\PalanqueesController;
use App\Http\Controllers\ParticipeController;
use App\Http\Controllers\PersonnesController;
use App\Http\Controllers\PlongeesController;
use App\Models\Adherent;
use App\Models\Bateau;
use App\Models\Inclut;
use App\Models\Lieu;
use App\Models\Moment;
use App\Models\Niveau;
use App\Models\Palanquee;
use App\Models\Participe;
use App\Models\Personne;
use App\Models\Plongee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\RedirectIfAuthenticated;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Function to create token, in order to use the website

// Route::get('/token/create', function (Request $request) {
//     $user= $request->user();
//     if (!is_null($user)) {
//         $abilities = ['get', 'post'];
//         if ($user->isDirector() || $user->isSecretary()) {
//             $abilities = array_merge($abilities, ['delete', 'put']);
//         }
//         $token = $user->createToken("token-name", $abilities);
//         dd($token->accessToken->original);
//         return redirect("/accueil");
//     }
//     return redirect("/connexion");
// });

Route::get('/token/create', function (Request $request) {
    $token = $request->user()->createToken('token', ['delete', 'put']);
    return ['token' => $token->plainTextToken];
});

Route::get('/token/test', function (Request $request) {
    $can = false;
    foreach ($request->user()->tokens as $token) {
        foreach ($token->abilities as $ability) {
            if ($ability == 'post')
                $can = true;
        }
    }
    return $can;
})->middleware(['auth:sanctum', 'ability:get']);

Route::middleware('ability:post')->get('/token/test2', function () {
    dd("test");
});

Route::get('/token/delete', function (Request $request) {
    $user = $request->user();
    if (!is_null($user)) {
        $user->tokens()->delete();
        return redirect("/accueil");
    }
    return redirect("/connexion");
});

Route::get("niveaux", [NiveauxController::class, 'index']); //Unit test
Route::get("niveaux/{id}", function (Niveau $id) { ///Unit test
    return (new NiveauxController())->show($id);
});
Route::get("niveaux/{id}/adherents", function (Niveau $id) { //Unit test
    return (new NiveauxController())->showAdherents($id);
});

Route::get("moments", [MomentsController::class, 'index']); //Unit test
Route::get("moments/{id}", function (Moment $id) { //Unit test
    return (new MomentsController())->show($id);
});

Route::get("bateaux", [BateauxController::class, 'index']); //Unit test
Route::get("bateaux/inactifs", [BateauxController::class, 'indexInactive']); //Unit test
Route::get("bateaux/{id}", function (Bateau $id) { //Unit test
    return (new BateauxController())->show($id);
});


Route::get("lieux", [LieuxController::class, 'index']); //Unit test
Route::get("lieux/inactifs", [LieuxController::class, 'indexInactive']); //Unit test
Route::get("lieux/{id}", function (Lieu $id) { //Unit test
    return (new LieuxController())->show($id);
});

Route::get("personnes", [PersonnesController::class, 'index']); //Unit test
Route::get("personnes/inactifs", [PersonnesController::class, 'indexInactive']); //Unit test
Route::get("personnes/{id}", function (Personne $id) { //Unit test
    return (new PersonnesController())->show($id);
});

Route::get("adherents", [AdherentsController::class, 'index']);  //Unit test
Route::get("adherents/inactifs", [AdherentsController::class, 'indexInactive']); //Unit test
Route::get("adherents/details", [AdherentsController::class, 'indexWithDetails']); //Unit test
Route::get("adherents/{id}", function (Adherent $id) { //Unit test
    return (new AdherentsController())->show($id);
});
Route::get("adherents/{id}/details", function (Adherent $id) { //Unit test
    return (new AdherentsController())->showWithDetails($id);
});

Route::get("plongees", [PlongeesController::class, 'index']);  //Unit test
Route::get("plongees/inactives", [PlongeesController::class, 'indexInactive']);  //Unit test
Route::get("plongees/details", [PlongeesController::class, 'indexWithDetails']);  //Unit test
Route::get("plongees/{id}", function (Plongee $id) {  //Unit test
    return (new PlongeesController())->show($id);
});
Route::get("plongees/{id}/details", function (Plongee $id) {  //Unit test
    return (new PlongeesController())->showWithDetails($id);
});
Route::get("plongees/{id}/participants", function (Plongee $id) { //Unit test
    return (new plongeesController())->participants($id);
});

Route::get("plongees/{id}/palanquees", function (Plongee $id) { //Unit test
    return (new plongeesController())->palanquees($id);
});

Route::get("participants", [ParticipeController::class, 'index']); //Unit test
Route::get("participants/details", [ParticipeController::class, 'indexWithDetails']); //Unit test
Route::get("participants/{id}", function (Participe $id) { //Unit test
    return (new ParticipeController())->show($id);
});
Route::get("participants/{id}/details", function (Participe $id) { //Unit test
    return (new ParticipeController())->showWithDetails($id);
});

Route::get("palanquees/membres/{id}", function (Inclut $id) { //Unit test
    return (new InclutController())->show($id);
});
Route::get("palanquees/membres/{id}/details", function (Inclut $id) { //Unit test
    return (new InclutController())->showWithDetails($id);
});

Route::get("palanquees", [PalanqueesController::class, 'index']); //Unit test
Route::get("palanquees/details", [PalanqueesController::class, 'indexWithDetails']); //Unit test
Route::get("palanquees/{id}", function (Palanquee $id) { //Unit test
    return (new PalanqueesController())->show($id);
});
Route::get("palanquees/{id}/details", function (Palanquee $id) { //Unit test
    return (new PalanqueesController())->showWithDetails($id);
});
Route::get("palanquees/{id}/membres", function (Palanquee $id) { //Unit test
    return (new PalanqueesController())->members($id);
});
Route::get("palanquees/{id}/membres/details", function (Palanquee $id) { //Unit test
    return (new PalanqueesController())->membersWithDetails($id);
});

Route::get("palanquees/membres/{id}", function (Inclut $id) { //Unit test
    return (new InclutController())->show($id);
});
Route::get("palanquees/membres/{id}/details", function (Inclut $id) { //Unit test
    return (new InclutController())->showWithDetails($id);
});

/* No use (get all members of all palanquees) - deactivated
    Route::get("palanquees/membres", [InclutController::class, 'index']); //tested manually
    Route::get("palanquees/membres/details", [InclutController::class, 'indexWithDetails']); //tested manually
    */

Route::post("bateaux", function (Request $request) { //Unit test
    return (new BateauxController())->store($request);
});

Route::post("lieux", function (Request $request) { //Unit test
    return (new LieuxController())->store($request);
});

Route::post("personnes", function (Request $request) { //Unit test
    return (new PersonnesController())->store($request);
});

Route::post("adherents", function (Request $request) { //Unit test
    return (new AdherentsController())->store($request);
});

Route::post("plongees/{id}/palanquees", function (Request $request, Plongee $id) { //Unit test
    return (new PalanqueesController())->store($request, $id);
});
Route::post("plongees", function (Request $request) {  //Unit test
    return (new PlongeesController())->store($request);
});

Route::post("participants", function (Request $request) { //Unit test
    return (new ParticipeController())->store($request);
});

Route::post("palanquees/{id}/membres", function (Request $request, Palanquee $id) { //Unit test
    return (new InclutController())->store($request, $id);
});

Route::post('database/seed/{dives}/{divers}', function (int $dives, int $divers) {
    \Database\Seeders\TestPlongeeSeeder::run($dives, $divers);
    return Response()->make("Il y a maintenant " . Plongee::count('PLO_id') . " plongées dans la base", 200);
});

Route::middleware(['auth:sanctum', 'ability:put'], function () {
    Route::put("bateaux", function (Request $request) {  //Unit test
        return (new BateauxController())->update($request);
    });

    Route::put("bateaux/{id}", function (Request $request, Bateau $id) {  //Unit test
        return (new BateauxController())->updateWithId($request, $id);
    });
    Route::put("lieux", function (Request $request) { //Unit test
        return (new LieuxController())->update($request);
    });
    Route::put("lieux/{id}", function (Request $request, Lieu $id) { //Unit test
        return (new LieuxController())->updateWithId($request, $id);
    });

    Route::put("bateaux/{id}", function (Request $request, Bateau $id) {  //Unit test
        return (new BateauxController())->updateWithId($request, $id);
    });
    Route::put("lieux", function (Request $request) { //Unit test
        return (new LieuxController())->update($request);
    });
    Route::put("lieux/{id}", function (Request $request, Lieu $id) { //Unit test
        return (new LieuxController())->updateWithId($request, $id);
    });

    Route::put("personnes", function (Request $request) { //Unit test
        return (new PersonnesController())->update($request);
    });
    Route::put("personnes/{id}", function (Request $request, Personne $id) { //Unit test
        return (new PersonnesController())->updateWithId($request, $id);
    });

    Route::put("adherents", function (Request $request) { //Unit test
        return (new AdherentsController())->update($request);
    });
    Route::put("adherents/{id}", function (Request $request, Adherent $id) { //Unit test
        return (new AdherentsController())->updateWithId($request, $id);
    });

    Route::put("plongees", function (Request $request) {  //Unit test
        return (new PlongeesController())->update($request);
    });
    Route::put("plongees/{id}", function (Request $request, Plongee $id) {  //Unit test
        return (new PlongeesController())->updateWithId($request, $id);
    });

    Route::put("palanquees/membres/{id}", function (Request $request, Inclut $id) { //Unit test
        return (new InclutController())->update($request, $id);
    });

    Route::put("palanquees/{id}", function (Request $request, Palanquee $id) { //Not tested
        return (new PalanqueesController())->updateWithId($request, $id);
    });

    /* No meaning - deactivated
    Route::put("participants", function (Request $request) { //tested manually
        return (new ParticipeController())->update($request);
    });
    Route::put("participants/{id}", function (Request $request, Participe $id) { //tested manually
        return (new ParticipeController())->updateWithId($request, $id);
    });
    */
});

Route::middleware(['auth:sanctum', 'ability:delete'], function () {
    Route::delete("bateaux/{id}", function (Request $request, Bateau $id) { //Unit test
        return (new BateauxController())->destroy($request, $id);
    });
    Route::delete("lieux/{id}", function (Request $request, Lieu $id) { //Unit test
        return (new LieuxController())->destroy($request, $id);
    });
    Route::delete("personnes/{id}", function (Request $request, Personne $id) { //Unit test
        return (new PersonnesController())->destroy($request, $id);
    });

    Route::delete("adherents/{id}", function (Request $request, Adherent $id) { //Unit test
        return (new AdherentsController())->destroy($request, $id);
    });
    Route::delete("plongees/{id}/participants/{adherent}", function (Request $request, Plongee $id, Adherent $adherent) { //Unit test
        return (new plongeesController())->removeParticipant($request, $id, $adherent);
    });
    Route::delete("plongees/{id}", function (Request $request, Plongee $id) { //Unit test
        return (new PlongeesController())->destroy($request, $id);
    });
    Route::delete("participants/{id}", function (Request $request, Participe $id) { //Unit test
        return (new ParticipeController())->destroy($request, $id);
    });

    Route::delete("palanquees/membres/{id}", function (Request $request, Inclut $id) { //Unit test
        return (new InclutController())->destroy($request, $id);
    });
    //see post("plongees/{id}/palanquees")
    Route::delete("palanquees/{id}", function (Request $request, Palanquee $id) { //Unit test
        return (new PalanqueesController())->destroy($request, $id);
    });
    Route::delete('database/fresh', function () {
        \Illuminate\Support\Facades\Artisan::call('migrate:fresh');
        return Response()->make("Base remise à zéro", 200);
    });
});
