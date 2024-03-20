<?php

namespace App\Http\Controllers;

use App\Models\Adherent;
use App\Models\Personne;
use App\Models\Niveau;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AdherentsController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return Response()->json(Adherent::with('personne')
            ->whereHas('personne', function($query) {$query->where('PER_active','1');})->get());
    }
    /**
     * Display a listing of the inactive resource.
     *
     * @return JsonResponse
     */
    public function indexInactive(): JsonResponse
    {
        return Response()->json(Adherent::with('personne')
            ->whereHas('personne', function($query) {$query->where('PER_active','0');})->get());
    }
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function indexWithDetails(): JsonResponse
    {
        return Response()->json(Adherent::with(['niveau','personne'])
            ->whereHas('personne', function($query) {$query->where('PER_active','1');})->get());
    }

    /**
     * Display the specified resource.
     *
     * @param Adherent $adherent
     * @return JsonResponse
     */
    public function show(Adherent $adherent): JsonResponse
    {
        return Response()->json($adherent->load(['personne']));
    }
    /**
     * Display the specified resource.
     *
     * @param Adherent $adherent
     * @return JsonResponse
     */
    public function showWithDetails(Adherent $adherent): JsonResponse
    {
        return Response()->json($adherent->load(['niveau','personne']));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create(): Response
    {
        return Response()->view('people/CreateAdherent');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $data = Validator::make($request->all(), [
            'nom' => 'required|max:45',
            'prenom' => ['required','max:45', Rule::unique('PLO_PERSONNES', 'PER_prenom')->where('PER_nom', $request->post('nom'))],
            'licence' => 'required|unique:PLO_ADHERENTS,ADH_licence|max:45',
            'date_certificat_medical' => 'date_format:Y-m-d',
            'pass' => ['required', 'confirmed' , 'max:60',
                Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
            'email' => 'required|email|unique:PLO_PERSONNES,PER_email|max:100',
            'forfait' => 'max:45',
            'niveau' => 'required|exists:PLO_NIVEAUX,NIV_id',
        ],[
            'prenom.unique' => 'Ce couple (nom, prénom) est déjà utilisé.'
        ])->validate();
        $person = new Personne();
        $person->PER_nom = $data['nom'];
        $person->PER_prenom = $data['prenom'];
        $person->PER_pass = password_hash($data['pass'], PASSWORD_DEFAULT);
        $person->PER_email = $data['email'];
        $person->save();

        $adherent = new Adherent();
        $adherent->ADH_id = $person->PER_id;
        $adherent->ADH_licence = $data['licence'];
        $adherent->ADH_date_certificat = Date::createFromFormat("Y-m-d",$data['date_certificat_medical']);
        $adherent->ADH_forfait = $data['forfait'];
        $adherent->ADH_niveau = $data['niveau'];
        $adherent->save();

        $adherent = Adherent::with('personne')->find($person->PER_id); // Seems that reload is mandatory to have the correct ADH_id
        if ($request->wantsJson())
            return Response()->json($adherent);
        else
            return Response()->redirectTo($request->session()->previousUrl())->with(["result" => "Création réussie"]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Adherent $adherent
     * @return RedirectResponse
     */
    public function edit(Adherent $adherent): RedirectResponse
    {
        return Response()->redirectToRoute("/adherents/".$adherent->ADH_id."/edition");
    }
    /**
     * Display the correct view with the data.
     *
     * @param Adherent $adherent who have to be edited
     * @return EditAdherent.blade.php view
     */
    public function editView(\App\Models\Adherent $id)
    {
        $active = Personne::All()->find($id)->PER_active;
        $levels = Niveau::all();
        return view("people/EditAdherent",["active" => $active,"levels" =>$levels ]);
    }
    public function listView(Request $request){
        $actives = $request->input('actives', session('aActive', 'true'))!='false';
        $sortOrder = $request->input('order', session('aOrder', 'nom'));
        $sortDir = $request->input('dir', session('aDir', 'false')) != 'false';
        session()->put([
            'aActives' => $actives?'true':'false',
            'aOrder' => $sortOrder,
            'aDir' => $sortDir?'true':'false'
            ]);

        $data = Adherent::with(['personne','niveau'])
            ->whereHas('personne', function ($query) use ($actives) {
                $query->where('PER_active', $actives);})->get()
            ->sortBy(['personne.PER_nom','personne.PER_prenom']);

        switch ($sortOrder) {
            case 'nom' : $data = $data->sortBy('personne.PER_nom', SORT_NATURAL, $sortDir); break;
            case 'prenom' : $data = $data->sortBy('personne.PER_prenom', SORT_NATURAL, $sortDir); break;
            case 'email' : $data = $data->sortBy('personne.PER_email', SORT_NATURAL, $sortDir); break;
            case 'niveau' : $data = $data->sortBy('ADH_niveau', SORT_NATURAL, $sortDir); break;
            case 'forfait' : $data = $data->sortBy('ADH_forfait', SORT_NATURAL, $sortDir); break;
        }

        return view("/people/ListAdherents", [
            'actives' => $actives,
            'sortOrder' => $sortOrder,
            'sortDir' => $sortDir,
            'data' => $data
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request the request with data
     * @return JsonResponse|RedirectResponse
     * @throws ValidationException
     */
    public function update(Request $request)
    {
        $id = $request->post('id'); // get id from body
        $data = $this->validateRequest($request, $id);
        $adherent = Adherent::find($data['id']);
        return $this->doUpdate($data, $adherent, $request);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request the request with data
     * @param Adherent $adherent the ship to update
     * @return JsonResponse|RedirectResponse
     * @throws ValidationException
     */
    public function updateWithId(Request $request, Adherent $adherent)
    {
        $id = $request->route()->id->ADH_id; // get id from the route
        $request->merge(["id"=>$id]); // in case it's not present in the body
        $data = $this->validateRequest($request, $id);
        return $this->doUpdate($data, $adherent, $request);
    }
    /**
     * @param array $data
     * @param Adherent $adherent
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     */
    public function doUpdate(array $data, Adherent $adherent, Request $request)
    {
        PersonnesController::doUpdate($data, $adherent->personne, $request);
        if (Adherent::find($adherent->ADH_id) == null)
            return Response()->redirectTo("/adherents"); // person has been deleted
        if (isset($data['licence']))
            $adherent->ADH_licence = $data['licence'];
        if (isset($data['date_certificat_medical']))
            $adherent->ADH_date_certificat = Date::createFromFormat("Y-m-d",$data['date_certificat_medical']);
        if (isset($data['forfait']))
            $adherent->ADH_forfait = $data['forfait'];
        if (isset($data['niveau']))
            $adherent->ADH_niveau = $data['niveau'];
        $adherent->save();
        if ($request->wantsJson())
            return Response()->json($adherent->load('personne'));
        else
            return Response()->redirectTo($request->session()->previousUrl())->with(["result" => "Édition réussie"]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param Adherent $adherent
     * @return Response|RedirectResponse|JsonResponse
     */
    public function destroy(Request $request, Adherent $adherent)
    {
        return PersonnesController::destroy($request, $adherent->personne);
        // Adherent is deleted by cascade of foreign key
    }

    /**
     * @param Request $request
     * @param $id
     * @return array
     * @throws ValidationException
     * @throws Exception
     */
    public function validateRequest(Request $request, $id): array
    {
        /** @var Adherent $adherent */
        $adherent = Adherent::with('personne')->find($id);
        $rules = [
            'id' => 'required|numeric|exists:PLO_ADHERENTS,ADH_id',
            'licence' => "nullable|unique:PLO_ADHERENTS,ADH_licence,$id,ADH_id|max:45",
            'date_certificat_medical' => 'nullable|date_format:Y-m-d',
            'token' => 'nullable',
            'old_pass' => ['nullable', Password::min(8)->letters()->mixedCase()->numbers()->symbols(),
                function ($attribute, $value, $fail) use ($adherent) {
                    if (($adherent != null) && (! app('hash')->check($value, $adherent->personne->PER_pass)))
                        $fail("L'ancien mot de passe est incorrect.");
                }],
            'pass' => ['nullable', 'confirmed',
                Password::min(8)->letters()->mixedCase()->numbers()->symbols(),
                function ($attribute, $value, $fail) use ($request) {
                    if (($request->user()==null || $request->user()->cannot('secretary')) &&
                        $request->missing('token') && $request->missing('old_pass'))
                        $fail("L'ancien mot de passe est requis pour modifier le mot de passe");
                }],
            'email' => "nullable|email|unique:PLO_PERSONNES,PER_email,$id,PER_id|max:100",
            'niveau' => 'nullable|exists:PLO_NIVEAUX,NIV_id',
        ];

        if ($request->has('nom') && $request->has('prenom'))
            $data = Validator::make($request->all(), array_merge($rules, [
                'nom' => 'nullable|max:45',
                'prenom' => ['nullable', 'max:45', Rule::unique('PLO_PERSONNES', 'PER_prenom')
                    ->ignore($id, 'PER_id')
                    ->where('PER_nom', $request->post('nom'))],
            ]),['prenom.unique' => 'Ce couple (nom, prénom) est déjà utilisé.'])->validate();
        elseif ($request->has('nom')) // no surname in data, consider surname from database
            $data = Validator::make($request->all(), array_merge($rules, [
                'nom' => ['max:45', Rule::unique('PLO_PERSONNES', 'PER_nom')
                    ->ignore($id, 'PER_id')
                    ->where('PER_prenom', $adherent->personne->PER_prenom)],
            ]),['nom.unique' => 'Ce couple (nom, prénom) est déjà utilisé.'])->validate();
        elseif ($request->has('prenom')) // no name in data, consider name from database
            $data = Validator::make($request->all(), array_merge($rules, [
                'prenom' => ['max:45', Rule::unique('PLO_PERSONNES', 'PER_prenom')
                    ->ignore($id, 'PER_id')
                    ->where('PER_nom', $adherent->personne->PER_nom)],
            ]),['prenom.unique' => 'Ce couple (nom, prénom) est déjà utilisé.'])->validate();
        else $data = $request->validate($rules);
        if (isset($data['token']) && $data['token'] != $adherent->personne->getRememberToken())
            throw new Exception("Invalid token");
        return $data;
    }

}
