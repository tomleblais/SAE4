<?php

namespace App\Http\Controllers;

use App\Models\Niveau;
use Illuminate\Http\JsonResponse;

class NiveauxController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return Response()->json(Niveau::all());
    }

    /**
     * Display the specified resource.
     *
     * @param Niveau $Niveau
     * @return JsonResponse
     */
    public function show(Niveau $Niveau): JsonResponse
    {
        return Response()->json($Niveau);
    }

    /**
     * Display the specified resource.
     *
     * @param Niveau $niveau
     * @return JsonResponse
     */
    public function showAdherents(Niveau $niveau): JsonResponse
    {
        return Response()->json($niveau->adherents);
    }

}
