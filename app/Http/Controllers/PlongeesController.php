<?php

namespace App\Http\Controllers;

use App\Models\Adherent;
use App\Models\Bateau;
use App\Models\Inclut;
use App\Models\Participe;
use App\Models\Lieu;
use App\Models\Bateau;
use App\Models\Moment;
use App\Models\Niveau;
use App\Models\Personne;
use App\Models\Plongee;
use Illuminate\Support\Facades\Auth;
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
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

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
    public function whatTheColor(Plongee $dive){
        $dive->isPast();
            
        $color="";
        if ($dive->isCancelled()) {
            $color = 'w3-blue-gray';
        } elseif ($dive->isLocked()) {
            $color = 'w3-purple';
        } else {
            $nbFree = $dive->nbFreeSlots();
            if ($nbFree <= 0)
                $color = 'w3-red';
            elseif ($nbFree<=5)
                $color = 'w3-yellow';
            else
                $color = 'w3-green';
        }
        return $color;
    }
    public static function getSortLink(string $title, string $field, string $order, bool $act, bool $dir) : string {
        if ($field === $order){
            return "&nbsp;<a href='?actives=".($act?'true':'false')."&order=$field&dir=".($dir?'false':'true')
                    ."'>$title &nbsp ".($dir?'v':'^')."</a>";
        } else {
            return "&nbsp;<a href='?actives=".($act?'true':'false')."&order=$field&dir=false'>$title &nbsp -</a>";
        }
    }
    public function listView(Request $request){
        $user = Auth::user();
        $actives = $request->input('actives', session('actives', 'true')) != 'false';
        $names=['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
        $sortOrder = $request->input('order', session('order', 'date'));
        $displayMonth = $request->input('mois', session('mois','cur'));
        if ($displayMonth == 'cur') $displayMonth = now()->month;
        $sortDir = $request->input('dir', session('dir', 'false')) != 'false';
        $req = Plongee::with(['lieu', 'niveau', 'moment','participants'])->where('PLO_active',$actives)->orderBy('PLO_date')->orderBy('PLO_moment');
        

        if (! $user->isDirector() && ! $user->isSecretary())
            $req->where('PLO_directeur', $user->PER_id);
        if ($displayMonth != 'tous')
            $req->whereMonth('PLO_date', $displayMonth);

        $dives = $req->get();
        session()->put([
            'actives' => $actives?'true':'false',
            'mois' => $displayMonth,
            'order' => $sortOrder,
            'dir' => $sortDir?'true':'false'
            ]);

        switch ($sortOrder) {
            case 'date' : if ($sortDir) $dives = $dives->reverse() ;break;
            case 'lieu' : $dives = $dives->sortBy('lieu.LIE_libelle', SORT_NATURAL, $sortDir); break;
            case 'niveau' : $dives = $dives->sortBy('niveau.NIV_niveau', SORT_NATURAL, $sortDir); break;
            case 'effectif' : $dives = $dives->sortBy(function ($v, $k)
                    { return $v->participants->count(); }, SORT_NATURAL, $sortDir); break;
            case 'etat' : $dives = $dives->sortBy('PLO_etat', SORT_NATURAL, $sortDir);
        }

        $usedMonths = DB::select("SELECT distinct month(PLO_date) as month
            FROM PLO_PLONGEES WHERE PLO_active = :act
            ORDER BY month", ['act'=>$actives?1:0]);
        
        return view("/dives/manage", ['user' => $user,
            'actives' => $actives,
            'displayMonth' => $displayMonth,
            'sortOrder' => $sortOrder,
            'sortDir' => $sortDir,
            'dives' => $dives,
            'names' => $names,
            'usedMonths' => $usedMonths,
            'instance' => $this
            ]
        );
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
    public function createView()
    {
        $pilotes = Personne::whereHas('autorisations', function ($query){
            $query->where('AUT_pilote', true);
        })->get();
        $securites = Personne::whereHas('autorisations', function ($query){
            $query->where('AUT_securite_surface', true);
        })->get();
        $directeurs = Adherent::with('personne')->whereHas('niveau', function ($query){
            $query->where('NIV_directeur', true);
        })->get();
        //dd($pal);
        return view("dives/CreateDive",
                ["pilotes"=>$pilotes,
                "securites"=>$securites,
                "directeurs"=>$directeurs,
                "Lieu"=>Lieu::all(),
                "Bateau"=>Bateau::all(),
                "Moment"=>Moment::all(),
                "Niveau"=>Niveau::all()]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function CreateGroup(Plongee $id)
    {
        
        //dd($id);
        $dive = Plongee::with('niveau', 'bateau', 'moment', 'lieu', 'etat',
            'palanquees.members.adherent', 'participants.niveau')->find($id->PLO_id);
        $participants = $dive->participants()->with('personne', 'niveau')->orderBy("ADH_niveau")->get();
        //dd($dive);
            return view("dives/ShowDive",
            ['plongee' => $id,'dive' => $dive, "participants"=>$participants
        ]);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public static function isEmpty(Plongee $dive,Adherent $adherent)
    {
        return empty(DB::select(
            "SELECT INC_id FROM PLO_INCLUT
            JOIN PLO_PALANQUEES ON INC_palanquee=PAL_id
            WHERE PAL_plongee=:plongee AND INC_adherent=:adherent",
            ['plongee'=>$dive->PLO_id, 'adherent'=>$adherent->ADH_id]));
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
            'bateau' => ['required','numeric','exists:PLO_BATEAUX,BAT_id',
            function($attribute, $value, $fail) use ($request) {
                $bateau = Bateau::find($value);

                if($bateau && $bateau->BAT_max_personnes < $request->input('max_plongeurs')){
                    $fail('La capacité du bateau est insuffisante pour le nombre maximum de plongeurs spécifié.');
                }
            }
        ],
            'date' => 'required|date_format:Y-m-d',
            'moment' => 'required|numeric|exists:PLO_MOMENTS,MOM_id',
            'min_plongeurs' => 'required|numeric|min:2',
            'max_plongeurs' => 'required|numeric|gte:min_plongeurs',
            'niveau' => 'required|numeric|exists:PLO_NIVEAUX,NIV_id',
            'pilote' => ['nullable', 'numeric', 'exists:PLO_PERSONNES,PER_id',
                'valid'=>Rule::exists('PLO_AUTORISATIONS', 'AUT_personne')->where('AUT_pilote', 1),
                'different' => function($attribute, $value, $fail) use ($request) {
                    if($request->input('pilote') == $request->input('securite_de_surface') ||  
                    $request->input('pilote') == $request->input('directeur_de_plongee')){
                        $fail('Le pilote, la securite de surface et le directeur de plongee doivent être 3 personnes differentes');
                    }
                }
            ],
            'securite_de_surface' => ['required', 'numeric', 'exists:PLO_PERSONNES,PER_id',
                'valid'=>Rule::exists('PLO_AUTORISATIONS', 'AUT_personne')
                    ->where('AUT_securite_surface', 1),
                'different' => function($attribute, $value, $fail) use ($request) {
                    if($request->input('securite_de_surface') == $request->input('pilote') ||  
                    $request->input('securite_de_surface') == $request->input('directeur_de_plongee')){
                        $fail('Le pilote, la securite de surface et le directeur de plongee doivent être 3 personnes differentes');
                    }
                }
            ],
            'directeur_de_plongee' => ['required', 'numeric', 'bail', 'exists:PLO_ADHERENTS,ADH_id',
                'valid'=>function($attribute, $value, $fail) {
                    if (! Adherent::find($value)->niveau->NIV_directeur)
                        $fail('Le directeur de plongée doit être de niveau suffisant (E4).');
                },
                'different' => function($attribute, $value, $fail) use ($request) {
                    if($request->input('directeur_de_plongee') == $request->input('securite_de_surface') ||  
                    $request->input('directeur_de_plongee') == $request->input('pilote')){
                        $fail('Le pilote, la securite de surface et le directeur de plongee doivent être 3 personnes differentes');
                    }
                }
            ],
        ], ['pilote.valid'=>"Le pilote doit être autorisé.",
            'securite_de_surface.valid' => 'La sécurité de surface doit être autorisée.',
            'directeur_de_plongee' => 'Le directeur de plongée doit être de niveau suffisant (E4).',
            'pilote.different' => 'Le pilote, la securité de surface et le directeur de plongee doivent être 3 personnes différentes'
        ]);
        
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

        if ($request->wantsJson()) {
            return Response()->json($dive);
        } else {
            return Response()->redirectTo($request->session()->previousUrl())->with(["result" => "Création réussie"]);
        }
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
     * Display the correct view with the data.
     *
     * @param Plongee $Plongee who have to be edited
     * @return EditPalanquee.blade.php view
     */
    public function editView(\App\Models\Plongee $id)
    {
        $active = Plongee::All()->find($id)->PLO_active;
        $pilotes = Personne::whereHas('autorisations', function ($query){
            $query->where('AUT_pilote', true);
        })->get();
        $securites = Personne::whereHas('autorisations', function ($query){
            $query->where('AUT_securite_surface', true);
        })->get();
        $directeurs = Adherent::with('personne')->whereHas('niveau', function ($query){
            $query->where('NIV_directeur', true);
        })->get();
        //dd($pal);
        return view("dives/EditDive",
                [ "active"=>$active,
                "pilotes"=>$pilotes,
                "securites"=>$securites,
                "directeurs"=>$directeurs,
                "Lieu"=>Lieu::all(),
                "Bateau"=>Bateau::all(),
                "Moment"=>Moment::all(),
                "Niveau"=>Niveau::all()]);
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
                function($attribute, $value, $fail) use ($request){
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
            'niveau' => [
                'nullable',
                'numeric',
                'exists:PLO_NIVEAUX,NIV_id',
                'valid' => function ($attribute, $value, $fail) use ($request) {
                    $adherents = Plongee::where("PLO_id", $request->input("id"))
                        ->join("PLO_participe", "PLO_plongees.PLO_id", "=", "PLO_participe.PAR_id")
                        ->join("PLO_adherents", "PLO_participe.PAR_adherent", "=", "PLO_adherents.ADH_id")
                        ->where("ADH_niveau", "<", $value)
                        ->count();

                    if ($adherents > 0)
                        $fail("Il y a au moins un participant à un niveau trop bas pour monter le niveau de la plongée.");
                }],
            'pilote' => ['nullable', 'numeric', 'exists:PLO_PERSONNES,PER_id',
                'valid'=>Rule::exists('PLO_AUTORISATIONS', 'AUT_personne')->where('AUT_pilote', 1),
                'different' => function($attribute, $value, $fail) use ($request){
                    if($request->input('pilote') == $request->input('securite_de_surfance') || 
                    $request->input('securite_de_surfance') == $request->input('directeur_de_plongee') || 
                    $request->input('pilote') == $request->input('directeur_de_plongee')){
                        $fail('Le pilote, la securite de surface et le directeur de plongee doivent être 3 personnes differentes');
                    }
                }
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
        ], [
            'pilote.valid'=>"Le pilote doit être autorisé.",
            'securite_de_surface.valid' => 'La sécurité de surface doit être autorisée.',
            'niveau.valid' => 'Il y a au moins un participant à un niveau trop bas pour monter le niveau de la plongée.',
            'bateau.valid' => "Il n'y a pas assez de place sur le bateau."
        ]);
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
