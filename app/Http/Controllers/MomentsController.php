<?php

namespace App\Http\Controllers;


use App\Models\Moment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class MomentsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return Response()->json(Moment::all());
    }

    /**
     * Display the specified resource.
     *
     * @param Moment $roles
     * @return Response
     */
    public function show(Moment $roles): Response
    {
        return Response($roles);
    }

}
