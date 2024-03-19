<?php

namespace App\Http\Controllers;

use App\Models\Adherent;
use App\Models\Inclut;
use App\Models\Participe;
use App\Models\Plongee;
use DateTime;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Throwable;

class PlongeesController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return Response()->json(Plongee::where('PLO_active','1')->orderBy('PLO_date')->get());
    }

    /**
     * Display a listing of the inactive resource.
     *
     * @return JsonResponse
     */
    public function indexInactive(): JsonResponse
    {
        return Response()->json(Plongee::all()->where('PLO_active','0'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function indexWithDetails(): JsonResponse
    {
        return Response()->json(Plongee::with(['bateau','moment','niveau','lieu','pilote','securite'])
            ->where('PLO_active','1')->get());
    }

    /**
     * Display a listing of the resource's groups.
     *
     * @param Plongee $dive
     * @return JsonResponse
     */
    public function palanquees(Plongee $dive): JsonResponse
    {
        return Response()->json($dive->palanquees);
    }

    /**
     * Display a listing of the resource's participants.
     *
     * @param Plongee $dive
     * @return JsonResponse
     */
    public function participants(Plongee $dive): JsonResponse
    {
        return Response()->json($dive->participants()->with('personne','niveau')->get());
    }

    /**
     * Display the specified resource.
     *
     * @param Plongee $plongee
     * @return JsonResponse
     */
    public function show(Plongee $plongee): JsonResponse
    {
        return Response()->json($plongee);
    }

    /**
     * Display the specified resource.
     *
     * @param Plongee $plongee
     * @return JsonResponse
     */
    public function showWithDetails(Plongee $plongee): JsonResponse
    {
        return Response()->json($plongee->load(['bateau','moment','niveau','lieu','pilote','securite']));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create(): Response
    {
        return Response()->view('dives/CreateDive');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     */
    public function store(Request $request)
    {
        $data = Validator::validate($request->all(), [
            'lieu' => 'required|numeric|exists:PLO_LIEUX,LIE_id',
            'bateau' => 'required|numeric|exists:PLO_BATEAUX,BAT_id',
            'date' => 'required|date_format:Y-m-d',
            'moment' => 'required|numeric|exists:PLO_MOMENTS,MOM_id',
            'min_plongeurs' => 'required|numeric|min:2',
            'max_plongeurs' => 'required|numeric|gte:min_plongeurs',
            'niveau' => 'required|numeric|exists:PLO_NIVEAUX,NIV_id',
            'pilote' => ['required', 'numeric', 'exists:PLO_PERSONNES,PER_id',
                'valid'=>Rule::exists('PLO_AUTORISATIONS', 'AUT_personne')->where('AUT_pilote', 1)
            ],
            'securite_de_surface' => ['required', 'numeric', 'exists:PLO_PERSONNES,PER_id',
                'valid'=>Rule::exists('PLO_AUTORISATIONS', 'AUT_personne')
                    ->where('AUT_securite_surface', 1)
            ],
            'directeur_de_plongee' => ['required', 'numeric', 'bail', 'exists:PLO_ADHERENTS,ADH_id',
                'valid'=>function($attribute, $value, $fail) {
                    if (! Adherent::find($value)->niveau->NIV_directeur)
                        $fail('Le directeur de plongée doit être de niveau suffisant (E4).');
                }
            ],
        ], ['pilote.valid'=>"Le pilote doit être autorisé.",
            'securite_de_surface.valid' => 'La sécurité de surface doit être autorisée.']);
        $dive = new Plongee();
        $dive->PLO_lieu = $data['lieu'];
        $dive->PLO_bateau = $data['bateau'];
        $dive->PLO_date = DateTime::createFromFormat("Y-m-d",$data['date']);
        $dive->PLO_moment = $data['moment'];
        $dive->PLO_min_plongeurs = $data['min_plongeurs'];
        $dive->PLO_max_plongeurs = $data['max_plongeurs'];
        $dive->PLO_niveau = $data['niveau'];
        $dive->PLO_active = 1;
        $dive->PLO_etat = 1; // Created
        $dive->PLO_pilote = $data['pilote'];
        $dive->PLO_securite = $data['securite_de_surface'];
        $dive->PLO_directeur = $data['directeur_de_plongee'];
        $dive->save();
        if ($request->wantsJson())
            return Response()->json($dive);
        else
            return Response()->redirectTo($request->session()->previousUrl())->with(["result" => "Création réussie"]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Plongee $plongee
     * @return RedirectResponse
     */
    public function edit(Plongee $plongee): RedirectResponse
    {
        return Response()->redirectToRoute("/dives/".$plongee->PLO_id."/showDive");
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request the request with data
     * @return JsonResponse|RedirectResponse
     * @throws ValidationException if request is not valid
     * @throws Throwable if delete fails
     */
    public function update(Request $request)
    {
        $data = $this->validateRequest($request);
        $plongee = Plongee::find($data['id']);
        return $this->doUpdate($data, $plongee, $request);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request the request with data
     * @param Plongee $plongee the ship to update
     * @return JsonResponse|RedirectResponse
     * @throws ValidationException if request is not valid
     * @throws Throwable if delete fails
     */
    public function updateWithId(Request $request, Plongee $plongee)
    {
        $id = $plongee->PLO_id; // get id from the route
        $request->merge(["id"=>$id]); // in case it's not present in the body
        $data = $this->validateRequest($request);
        return $this->doUpdate($data, $plongee, $request);
    }

    /**
     * @param array $data
     * @param Plongee $dive
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     * @throws Throwable if delete fails
     */
    public function doUpdate(array $data, Plongee $dive, Request $request)
    {
        if ($request->has('deleteDive')) {
            self::destroy($request, $dive);
            return Response()->redirectTo("/plongees");
        }
        if ($request->has('restoreDive')) {
            $dive->PLO_active = true;
        }

        if (isset($data['lieu']))
            $dive->PLO_lieu = $data['lieu'];
        if (isset($data['bateau']))
            $dive->PLO_bateau = $data['bateau'];
        if (isset($data['date']))
            $dive->PLO_date = Date::createFromFormat("Y-m-d",$data['date']);
        if (isset($data['moment']))
            $dive->PLO_moment = $data['moment'];
        if (isset($data['min_plongeurs']))
            $dive->PLO_min_plongeurs = $data['min_plongeurs'];
        if (isset($data['max_plongeurs']))
            $dive->PLO_max_plongeurs = $data['max_plongeurs'];
        if (isset($data['niveau']))
            $dive->PLO_niveau = $data['niveau'];
        if (isset($data['etat']))
            $dive->PLO_etat = $data['etat'];
        if (isset($data['pilote']))
            $dive->PLO_pilote = $data['pilote'];
        if (isset($data['securite_de_surface']))
            $dive->PLO_securite = $data['securite_de_surface'];
        if (isset($data['directeur_de_plongee']))
            $dive->PLO_directeur = $data['directeur_de_plongee'];
        $dive->save();
        if ($request->wantsJson())
            return Response()->json($dive);
        else
            return Response()->redirectTo($request->session()->previousUrl())->with(["result" => "Édition réussie"]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param Plongee $plongee
     * @return Response|RedirectResponse|JsonResponse
     * @throws Throwable if delete fails
     */
    public function destroy(Request $request, Plongee $plongee)
    {
        if ($plongee->PLO_active) {
            $plongee->PLO_active = false;
            $plongee->save();
            if ($request->wantsJson())
                return Response()->noContent(ResponseAlias::HTTP_OK);
            else
                return Response()->redirectTo($request->session()->previousUrl())->with(
                    ["result" => "Plongée marquée inactive"]);
        } else {
            /*
             * @var $date Date
             */
            $date = $plongee->PLO_date;
            $interval = $date->diff(now());
            if ($interval->y < 1) {
                if ($request->wantsJson())
                    return Response()->make("La plongée n'est pas encore dépassée.", 500);
                else
                    return Response()->redirectTo($request->session()->previousUrl())
                        ->with(["result" => "La plongée n'est pas encore dépassée"]);
            }
            $plongee->deleteOrFail();
            if ($request->wantsJson())
                return Response()->noContent(ResponseAlias::HTTP_OK);
            else
                return Response()->redirectTo($request->session()->previousUrl())
                    ->with(["result" => "Plongée supprimée"]);
        }
    }

    /**
     * @param Request $request
     * @return array
     */
    public function validateRequest(Request $request): array
    {
        return Validator::validate($request->all(),[
            'id' => 'required|numeric|exists:PLO_PLONGEES,PLO_id',
            'lieu' => 'nullable|numeric|exists:PLO_LIEUX,LIE_id',
            'bateau' => ['nullable','numeric','exists:PLO_BATEAUX,BAT_id',
                function($attribute, $value, $fail){
                    $bateau = Bateau::find($value);

                    if($bateau && $bateau->BAT_max_personnes < $request->input('max_plongeurs')){
                        $fail('La capacité du bateau est insuffisante pour le nombre maximum de plongeurs spécifié.');
                    }
                }
        ],
            'date' => 'nullable|date_format:Y-m-d',
            'moment' => 'nullable|numeric|exists:PLO_MOMENTS,MOM_id',
            'min_plongeurs' => 'nullable|numeric|min:2',
            'max_plongeurs' => 'nullable|numeric|gte:min_plongeurs',
            'niveau' => 'nullable|numeric|exists:PLO_NIVEAUX,NIV_id',
            'pilote' => ['nullable', 'numeric', 'exists:PLO_PERSONNES,PER_id',
                'valid'=>Rule::exists('PLO_AUTORISATIONS', 'AUT_personne')->where('AUT_pilote', 1)
            ],
            'securite_de_surface' => ['nullable', 'numeric', 'exists:PLO_PERSONNES,PER_id',
                'valid'=>Rule::exists('PLO_AUTORISATIONS', 'AUT_personne')
                    ->where('AUT_securite_surface', 1)
            ],
            'directeur_de_plongee' => ['nullable', 'numeric', 'bail', 'exists:PLO_ADHERENTS,ADH_id',
                'valid'=>function($attribute, $value, $fail) {
                    if (! Adherent::find($value)->niveau->NIV_directeur)
                        $fail('Le directeur de plongée doit être de niveau suffisant (E4).');
                }
            ],
            'etat' => 'nullable|numeric|exists:PLO_ETATS,ETA_id',
        ], ['pilote.valid'=>"Le pilote doit être autorisé.",
            'securite_de_surface.valid' => 'La sécurité de surface doit être autorisée.']);
    }  

    /**
     * @param Request $request
     * @param Plongee|null $dive
     * @param Adherent|null $adherent
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function removeParticipant(Request $request, ?Plongee $dive, ?Adherent $adherent)
    {
        if ($dive == null)
            throw new Exception("No valide dive.");
        if ($adherent == null)
            throw new Exception("No valide adherent.");
        $participate = Participe::where("PAR_plongee", $dive->PLO_id)
            ->where("PAR_adherent", $adherent->ADH_id)->get();
        if ($participate->isNotEmpty()) {
            /** @var Participe $elt */
            $elt = $participate->first();
            $elt->delete();
            $included = Inclut::with(['palanquee'=>function($query) use ($dive){
                    $query->where('PAL_plongee', $dive->PLO_id);
                }])->where('INC_adherent', $adherent->ADH_id)->get();
            foreach ($included as $member) {
                $member->delete();
            }
        }
        if ($request->wantsJson())
            return Response()->noContent(ResponseAlias::HTTP_OK);
        else
            return Redirect::back();
    }


}
