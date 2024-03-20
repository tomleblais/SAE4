<?php

namespace App\Http\Controllers;

use App\Models\Bateau;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Throwable;

class BateauxController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return Response()->json(Bateau::where('BAT_active','1')->get());
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function indexInactive(): JsonResponse
    {
        return Response()->json(Bateau::where('BAT_active','0')->get());
    }

    /**
     * Display the specified resource.
     *
     * @param Bateau $bateau
     * @return JsonResponse
     */
    public function show(Bateau $bateau): JsonResponse
    {
        return Response()->json(($bateau));
    }
   

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create(): Response
    {
        return Response()->view('ships/CreateShip');
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
            'libelle' => 'required|unique:PLO_BATEAUX,BAT_libelle|max:45',
            'max_personnes' => 'required|numeric|min:2'
        ]);
        $ship = new Bateau();
        $ship->BAT_libelle = $data['libelle'];
        $ship->BAT_max_personnes = $data['max_personnes'];
        $ship->BAT_active = true;
        $ship->save();
        if ($request->wantsJson())
            return Response()->json($ship);
        else
            return Response()->redirectTo($request->session()->previousUrl())->with(["result" => "Création réussie"]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Bateau $bateau
     * @return RedirectResponse
     */
    public function edit(Bateau $bateau): RedirectResponse
    {
        return Response()->redirectToRoute("/bateaux/".$bateau->BAT_id."/edition");
    }
    public function editView(\App\Models\Bateau $id)
    {
        $active = \App\Models\Bateau::All()->find($id)->BAT_active;
        return view("ships/EditShip",["active" => $active]);
    }
    public function listView(){
        if (!isset($actives))
            $actives = true;
        $data = Bateau::where('BAT_active', $actives)->get();
        return view("ships/ListShips", [
            'actives' => $actives,
            'data' => $data
        ]);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param Request $request the request with data
     * @return JsonResponse|RedirectResponse
     */
    public function update(Request $request)
    {
        $id = $request->input('id'); // get id from body
        $data = $request->validate([
            'id' => 'required|numeric|exists:PLO_BATEAUX,BAT_id',
            'libelle' => "nullable|unique:PLO_BATEAUX,BAT_libelle,$id,BAT_id|max:45", //unique but itself
            'max_personnes' => 'nullable|numeric|min:2'
        ]);

        $ship = Bateau::find($data['id']);
        return $this->doUpdate($data, $ship, $request);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request the request with data
     * @param Bateau $ship the ship to update
     * @return JsonResponse|RedirectResponse
     */
    public function updateWithId(Request $request, Bateau $ship)
    {
        $id = $request->route()->id->BAT_id; // get id from the route
        $data = $request->validate([
            'libelle' => "nullable|unique:PLO_BATEAUX,BAT_libelle,$id,BAT_id|max:45", // unique but itself
            'max_personnes' => 'nullable|numeric|min:2'
        ]);

        return $this->doUpdate($data, $ship, $request);
    }

    /**
     * @param array $data
     * @param Bateau $ship
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     */
    public function doUpdate(array $data, Bateau $ship, Request $request)
    {
        if ($request->has('deleteShip')) {
            self::destroy($request, $ship);
            return Response()->redirectTo("/bateaux");
        }
        if ($request->has('restoreShip')) {
            $ship->BAT_active = true;
        }
        if (isset($data['libelle']))
            $ship->BAT_libelle = $data['libelle'];
        if (isset($data['max_personnes']))
            $ship->BAT_max_personnes = $data['max_personnes'];

        $ship->save();
        if ($request->wantsJson())
            return Response()->json($ship);
        else
            return Response()->redirectTo($request->session()->previousUrl())->with(["result" => "Édition réussie"]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param Bateau $ship
     * @return Response|RedirectResponse|JsonResponse     * @throws \Throwable
     */
    public function destroy(Request $request, Bateau $ship)
    {
        try {
            $ship->deleteOrFail();
            if ($request->wantsJson())
                return Response()->noContent(ResponseAlias::HTTP_OK);
            else
                return Response()->redirectTo($request->session()->previousUrl())->with(["result" => "Bateau supprimé"]);
        } catch (Throwable $exception) {
            // the ship may have active dives -> set inactive
            $ship->BAT_active = false;
            $ship->save();
            if ($request->wantsJson())
                return Response()->json($ship);
            else
                return Response()->redirectTo($request->session()->previousUrl())->with(["result" => "Bateau marqué inactif"]);
        }
    }

}
