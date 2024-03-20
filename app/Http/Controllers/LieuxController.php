<?php

namespace App\Http\Controllers;

use App\Models\Lieu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Throwable;

class LieuxController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return Response()->json(Lieu::where('LIE_active', '1')->get());
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function indexInactive(): JsonResponse
    {
        return Response()->json(Lieu::where('LIE_active', '0')->get());
    }

    /**
     * Display the specified resource.
     *
     * @param Lieu $Lieu
     * @return JsonResponse
     */
    public function show(Lieu $Lieu): JsonResponse
    {
        return Response()->json(($Lieu));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create(): Response
    {
        return Response()->view('sites/CreateSite');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'libelle' => 'required|unique:PLO_LIEUX,LIE_libelle|max:45',
            'description' => 'required|max:100'
        ]);

        $site = new Lieu();
        $site->LIE_libelle = $data['libelle'];
        $site->LIE_description = $data['description'];
        $site->LIE_active = true;
        $site->save();

        if ($request->wantsJson())
            return Response()->json($site);
        else
            return Response()->redirectTo($request->session()->previousUrl())->with(["result" => "Création réussie"]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Lieu $lieu
     * @return RedirectResponse
     */
    public function edit(Lieu $lieu): RedirectResponse
    {
        return Response()->redirectToRoute("/lieux/".$lieu->LIE_id."/edition");
    }


    /**
     * Display the correct view with the data.
     *
     * @param Lieu $Lieu who have to be edited
     * @return EditSite.blade.php view
     */
    public function editView(\App\Models\Lieu $id)
    {
        $active = \App\Models\Lieu::All()->find($id)->LIE_active;
        return view("sites/EditSite",["active" => $active]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request the request with data
     * @return JsonResponse|RedirectResponse
     */
    public function update(Request $request)
    {
        $id = $request->post('id'); // get id from body
        $data = $request->validate([
            'id' => 'required|numeric|exists:PLO_LIEUX,LIE_id',
            'libelle' => "nullable|unique:PLO_LIEUX,LIE_libelle,$id,LIE_id|max:45", //unique but itself
            'description' => 'nullable|max:100'
        ]);

        $site = Lieu::find($data['id']);
        return $this->doUpdate($data, $site, $request);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request the request with data
     * @param Lieu $site the site to update
     * @return JsonResponse|RedirectResponse
     */
    public function updateWithId(Request $request, Lieu $site)
    {
        $id = $request->route()->id->LIE_id; // get id from the route
        $data = $request->validate([
            'libelle' => "nullable|unique:PLO_LIEUX,LIE_libelle,$id,LIE_id|max:45", // unique but itself
            'description' => 'nullable|max:100'
        ]);

        return $this->doUpdate($data, $site, $request);
    }

    /**
     * @param array $data the new data to update with
     * @param Lieu $site the site to update
     * @param Request $request the request
     * @return JsonResponse|RedirectResponse
     */
    public function doUpdate(array $data, Lieu $site, Request $request)
    {
        if ($request->has('deleteSite')) {
            self::destroy($request, $site);
            return Response()->redirectTo("/sites");
        }
        if ($request->has('restoreSite')) {
            $site->LIE_active = true;
        }
        if (isset($data['libelle']))
            $site->LIE_libelle = $data['libelle'];
        if (isset($data['description']))
            $site->LIE_description = $data['description'];

        $site->save();
        if ($request->wantsJson())
            return Response()->json($site);
        else
            return Response()->redirectTo($request->session()->previousUrl())->with(["result" => "Édition réussie"]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param Lieu $site
     * @return Response|RedirectResponse|JsonResponse     * @throws \Throwable
     */
    public function destroy(Request $request, Lieu $site)
    {
        try {
            $site->deleteOrFail();
            if ($request->wantsJson())
                return Response()->noContent(ResponseAlias::HTTP_OK);
            else
                return Response()->redirectTo($request->session()->previousUrl())->with(["result" => "Lieu supprimé"]);
        } catch (Throwable $exception) {
            // the ship may have active dives -> set inactive
            $site->LIE_active = false;
            $site->save();
            if ($request->wantsJson())
                return Response()->json($site);
            else
                return Response()->redirectTo($request->session()->previousUrl())->with(["result" => "Lieu marqué inactif"]);
        }
    }
}
