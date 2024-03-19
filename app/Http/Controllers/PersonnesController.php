<?php

namespace App\Http\Controllers;

use App\Models\Autorisations;
use App\Models\Personne;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Throwable;

class PersonnesController extends Controller
{
    /**
     * @param Personne $person
     * @param array $data
     * @return void
     */
    public static function updateRights(Personne $person, array $data): void
    {
        /** @var ?Autorisations $autorisations */
        $autorisations = null;
        if ($person->autorisations()->exists()) {
            $autorisations = $person->autorisations;
        } elseif (isset($data['directeur_de_section']) || isset($data['secretaire']) || isset($data['securite_de_surface']) || isset($data['pilote'])) {
            $person->autorisations()->create();
            $person->fresh(['autorisations']);
            $autorisations = $person->autorisations;
        }
        if (isset($data['directeur_de_section'])) {
            $autorisations->AUT_directeur_section = ($data['directeur_de_section'] == 'on' || $data['directeur_de_section'] == 'true');
        }
        if (isset($data['secretaire'])) {
            $autorisations->AUT_secretaire = ($data['secretaire'] == 'on' || $data['secretaire'] == 'true');
        }
        if (isset($data['securite_de_surface']))
            $autorisations->AUT_securite_surface = ($data['securite_de_surface'] == 'on' || $data['securite_de_surface'] == 'true');
        if (isset($data['pilote']))
            $autorisations->AUT_pilote = ($data['pilote'] == 'on' || $data['pilote'] == 'true');
        if (isset($autorisations)) {
            $autorisations->save();
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return Response()->json(Personne::where('PER_active','1')->get());
    }

    /**
     * Display a listing of the inactive resource.
     *
     * @return JsonResponse
     */
    public function indexInactive(): JsonResponse
    {
        return Response()->json(Personne::where('PER_active','0')->get());
    }

    /**
     * Display the specified resource.
     *
     * @param Personne $person
     * @return JsonResponse
     */
    public function show(Personne $person): JsonResponse
    {
        return Response()->json($person);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create(): Response
    {
        return Response()->view('people/CreatePerson');
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
            'pass' => ['required', 'confirmed' , 'max:60',
                Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
            'email' => 'required|email|unique:PLO_PERSONNES,PER_email|max:100',
            'directeur_de_section' => 'nullable|in:on,off,true,false',
            'secretaire' => 'nullable|in:on,off,true,false',
            'securite_de_surface' => 'nullable|in:on,off,true,false',
            'pilote' => 'nullable|in:on,off,true,false',
        ],[
            'prenom.unique' => 'Ce couple (nom, prénom) est déjà utilisé.'
        ])->validate();
        $person = new Personne();
        $person->PER_nom = $data['nom'];
        $person->PER_prenom = $data['prenom'];
        $person->PER_pass = Hash::make($data['pass']);
        $person->PER_email = $data['email'];
        $person->PER_active = true;
        $person->save();
        self::updateRights($person, $data);

        if ($request->wantsJson())
            return Response()->json($person);
        else
            return Response()->redirectTo($request->session()->previousUrl())->with(["result" => "Création réussie"]);
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
        $person = Personne::find($data['id']);
        return $this->doUpdate($data, $person, $request);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request the request with data
     * @param Personne $person the person to update
     * @return JsonResponse|RedirectResponse
     * @throws ValidationException
     */
    public function updateWithId(Request $request, Personne $person)
    {
        $id = $request->route()->id->PER_id; // get id from the route
        $request->merge(["id"=>$id]); // in case it's not present in the body
        $data = $this->validateRequest($request, $id);
        return $this->doUpdate($data, $person, $request);
    }

    /**
     * @param array $data
     * @param Personne $person
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     */
    public static function doUpdate(array $data, Personne $person, Request $request)
    {
        if ($request->has('deletePerson')) {
            self::destroy($request, $person);
            return Response()->redirectTo("/personnes");
        }
        if ($request->has('restorePerson')) {
            $person->PER_active = true;
        }
        if (isset($data['nom']))
            $person->PER_nom = $data['nom'];
        if (isset($data['prenom']))
            $person->PER_prenom = $data['prenom'];
        if (isset($data['pass']))
            $person->PER_pass = Hash::make($data['pass']);
        if (isset($data['email']))
            $person->PER_email = $data['email'];

        $person->save();
        self::updateRights($person, $data);

        if ($request->wantsJson())
            return Response()->json($person);
        else
            return Response()->redirectTo($request->session()->previousUrl())->with(["result" => "Édition réussie"]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param Personne $person
     * @return Response|RedirectResponse|JsonResponse
     */
    public static function destroy(Request $request, Personne $person)
    {
        try {
            $person->deleteOrFail();
            if ($request->wantsJson())
                return Response()->noContent(ResponseAlias::HTTP_OK);
            else
                return Response()->redirectTo($request->session()->previousUrl())
                    ->with(["result" => "Personne supprimée"]);
        } catch (Throwable $exception) {
            // the adherent may have active dives -> set inactive
            $person->PER_active = false;
            $person->save();
            if ($request->wantsJson())
                return Response()->json($person);
            else
                return Response()->redirectTo($request->session()->previousUrl())
                    ->with(["result" => "Personne marquée inactive"]);
        }
    }

    /**
     * @param Request $request
     * @param int $id
     * @return array
     * @throws ValidationException
     * @throws Exception
     */
    public function validateRequest(Request $request, int $id): array
    {
        $person = Personne::find($id);
        $rules = [
            'id' => 'required|numeric|exists:PLO_PERSONNES,PER_id',
            'token' => 'nullable',
            'old_pass' => ['nullable', Password::min(8)->letters()->mixedCase()->numbers()->symbols(),
                function ($attribute, $value, $fail) use ($person) {
                    if (($person != null) && (! app('hash')->check($value, $person->PER_pass)))
                        $fail("L'ancien mot de passe est incorrect.");
                }],
            'pass' => ['nullable', 'confirmed',
                Password::min(8)->letters()->mixedCase()->numbers()->symbols(),
                function ($attribute, $value, $fail) use ($request) {
                    if ((!Auth::hasUser() || Auth::user()->cannot('secretaire')) &&
                        ($request->missing('token') && $request->missing('old_pass')))
                        $fail("L'ancien mot de passe est requis pour modifier le mot de passe");
                }],
            'email' => ['nullable','email','max:100', Rule::unique('PLO_PERSONNES', 'PER_email')
                ->ignore($id, 'PER_id')],
            'directeur_de_section' => 'nullable|in:on,off,true,false',
            'secretaire' => 'nullable|in:on,off,true,false',
            'securite_de_surface' => 'nullable|in:on,off,true,false',
            'pilote' => 'nullable|in:on,off,true,false',
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
                    ->where('PER_prenom', $person->PER_prenom)],
            ]),['nom.unique' => 'Ce couple (nom, prénom) est déjà utilisé.'])->validate();
        elseif ($request->has('prenom')) // no name in data, consider name from database
            $data = Validator::make($request->all(), array_merge($rules, [
                'prenom' => ['max:45', Rule::unique('PLO_PERSONNES', 'PER_prenom')
                    ->ignore($id, 'PER_id')
                    ->where('PER_nom', $person->PER_nom)],
            ]),['prenom.unique' => 'Ce couple (nom, prénom) est déjà utilisé.'])->validate();
        else $data = $request->validate($rules);
        if (isset($data['token']) && $data['token'] != $person->getRememberToken())
            throw new Exception("Invalid token");
        return $data;
    }
}
