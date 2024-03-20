<?php

namespace App\Http\Controllers;

use App\Models\Palanquee;
use App\Models\Plongee;
use DateTime;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Throwable;

class PalanqueesController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return Response()->json(Palanquee::all());
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function indexWithDetails(): JsonResponse
    {
        return Response()->json(Palanquee::with('plongee.niveau:NIV_id,NIV_code', 'plongee.etat')->get());
    }

    /**
     * Display a listing of the resource's members.
     *
     * @param Palanquee $palanquee
     * @return JsonResponse
     */
    public function members(Palanquee $palanquee): JsonResponse
    {
        return Response()->json($palanquee->members);
    }

    /**
     * Display a listing of the resource's members.
     *
     * @param Palanquee $palanquee
     * @return JsonResponse
     */
    public function membersWithDetails(Palanquee $palanquee): JsonResponse
    {
        return Response()->json($palanquee->members()->with('adherent.niveau:NIV_id,NIV_code')->get());
    }

    /**
     * Display the specified resource.
     *
     * @param Palanquee $palanquee
     * @return JsonResponse
     */
    public function show(Palanquee $palanquee): JsonResponse
    {
        return Response()->json($palanquee);
    }

    /**
     * Display the specified resource.
     *
     * @param Palanquee $palanquee
     * @return JsonResponse
     */
    public function showWithDetails(Palanquee $palanquee): JsonResponse
    {
        return Response()->json($palanquee->load('plongee.niveau:NIV_id,NIV_code', 'plongee.etat'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @param Plongee $dive
     * @return JsonResponse|RedirectResponse
     */
    public function store(Request $request, Plongee $dive)
    {
        $data = $request->validate([
            'max_profondeur' => 'required|numeric|min:0|max:'.$dive->niveau->getMaxDepth(),
            'max_duree' => 'required|numeric|min:0'
        ]);
        $palanquee = new Palanquee();
        $palanquee->PAL_plongee = $dive->PLO_id;
        $palanquee->PAL_max_prof = $data['max_profondeur'];
        $palanquee->PAL_max_duree = $data['max_duree'];
        $palanquee->save();
        if ($request->wantsJson())
            return Response()->json($palanquee);
        else
            return Response()->redirectTo($request->session()->previousUrl())->with(["result" => "Création réussie"]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Palanquee $palanquee
     * @return Response
     * @throws BindingResolutionException
     */
    public function edit(Palanquee $palanquee): Response
    {
        switch ($palanquee->plongee->etat->ETA_id) {
            case 1:
            case 2: return Response()->view('palanquees/EditPalanquee', ['dive'=>$palanquee->plongee, 'pal'=>$palanquee]);
            default: return Response()->make("La plongée est déjà validée.", ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request the request with data
     * @return JsonResponse|RedirectResponse
     * @throws BindingResolutionException
     */
    public function update(Request $request)
    {
        $data = $this->validateRequest($request);
        $palanquee = Palanquee::find($data['id']);
        return $this->doUpdate($data, $palanquee, $request);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request the request with data
     * @param Palanquee $palanquee the palanquee to update
     * @return JsonResponse|RedirectResponse
     * @throws BindingResolutionException
     */
    public function updateWithId(Request $request, Palanquee $palanquee)
    {
        $id = $request->route()->id->PAL_id; // get id from the route
        $request->merge(["id"=>$id]); // in case it's not present in the body
        $data = $this->validateRequest($request);
        return $this->doUpdate($data, $palanquee, $request);
    }

    /**
     * @param array $data
     * @param Palanquee $palanquee
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     * @throws BindingResolutionException
     */
    public function doUpdate(array $data, Palanquee $palanquee, Request $request)
    {
        switch ($palanquee->plongee->etat->ETA_id) {
            case 1:
                return $this->doUpdateParameters($data, $palanquee, $request);
            case 2:
                return $this->doUpdateValidation($data, $palanquee, $request);
            default:
                return Response()->make("La plongée est déjà validée.", ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param array $data
     * @param Palanquee $palanquee
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     */
    public function doUpdateParameters(array $data, Palanquee $palanquee, Request $request)
    {
        if (isset($data['max_profondeur']))
            $palanquee->PAL_max_prof = $data['max_profondeur'];
        if (isset($data['max_duree']))
            $palanquee->PAL_max_duree = $data['max_duree'];

        $palanquee->save();
        if ($request->wantsJson())
            return Response()->json($palanquee);
        else
            return Response()->redirectTo($request->session()->previousUrl())->with(["result" => "Édition réussie"]);
    }

    /**
     * @param array $data
     * @param Palanquee $palanquee
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     */
    public function doUpdateValidation(array $data, Palanquee $palanquee, Request $request)
    {
        if (isset($data['heure_immersion']))
            $palanquee->PAL_heure_immersion = DateTime::createFromFormat("H:i", $data['heure_immersion']);
        if (isset($data['heure_sortie']))
            $palanquee->PAL_heure_sortie = DateTime::createFromFormat("H:i", $data['heure_sortie']);
        if (isset($data['profondeur_realisee']))
            $palanquee->PAL_prof_realisee = $data['profondeur_realisee'];
        if (isset($data['duree_realisee']))
            $palanquee->PAL_duree_realisee = $data['duree_realisee'];

        $palanquee->save();
        if ($request->wantsJson())
            return Response()->json($palanquee);
        else
            return Response()->redirectTo($request->session()->previousUrl())->with(["result" => "Édition réussie"]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param Palanquee $palanquee
     * @return RedirectResponse|Response
     * @throws Throwable
     */
    public function destroy(Request $request, Palanquee $palanquee)
    {
        $palanquee->deleteOrFail();
        if ($request->wantsJson())
            return Response()->noContent(ResponseAlias::HTTP_OK);
        else
            return Response()->redirectTo($request->session()->previousUrl())->with(["result" => "Palanquée supprimée"]);
    }

    /**
     * @param Request $request
     * @return array
     */
    public function validateRequest(Request $request): array
    {
        $rules = [
            'id' => 'required|numeric|exists:PLO_PALANQUEES,PAL_id',
            'max_profondeur' => 'numeric|min:0',
            'max_duree' => 'numeric|min:0',
            'heure_immersion' => 'date_format:H:i',
            'heure_sortie' => 'date_format:H:i',
            'profondeur_realisee' => 'numeric|min:0',
            'duree_realisee' => 'numeric|min:0',
        ];
        if ($request->has('id'))
            return $request->validateWithBag("palanquee".$request->input('id'), $rules);
        else
            return $request->validate($rules);
    }

}
