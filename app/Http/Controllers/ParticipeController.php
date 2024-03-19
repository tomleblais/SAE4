<?php

namespace App\Http\Controllers;

use App\Models\Adherent;
use App\Models\Participe;
use App\Models\Plongee;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ParticipeController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return Response()->json(Participe::all());
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function indexWithDetails(): JsonResponse
    {
        return Response()->json(Participe::with(['adherent.niveau','plongee.niveau','plongee.moment'])->get());
    }

    /**
     * Display the specified resource.
     *
     * @param Participe $participant
     * @return JsonResponse
     */
    public function show(Participe $participant): JsonResponse
    {
        return Response()->json($participant);
    }

    /**
     * Display the specified resource.
     *
     * @param Participe $participant
     * @return JsonResponse
     */
    public function showWithDetails(Participe $participant): JsonResponse
    {
        return Response()->json($participant->load(['adherent','plongee']));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create(): Response
    {
        return Response()->view('participant/CreateParticipant');
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
            'plongee' => 'required|numeric|exists:PLO_PLONGEES,PLO_id',
            'adherent' => ['required','numeric','bail','exists:PLO_ADHERENTS,ADH_id',
                function ($attribute, $value, $fail) use ($request) {
                    /** @var Adherent $adherent */
                    $adherent = Adherent::with('niveau:NIV_id,NIV_niveau')->find($value);
                    /** @var Plongee $dive */
                    $dive = Plongee::with('niveau')->find($request->post('plongee'));
                    $niveau = $dive->niveau->NIV_niveau;
                    if ($adherent->niveau->NIV_niveau < $niveau)
                        $fail('Le niveau du participant doit être au moins '.$dive->niveau->NIV_code);
                },
                Rule::unique('PLO_PARTICIPE', 'PAR_adherent') // only one participation per (adherent,dive)
                    ->where('PAR_plongee', $request->post('plongee'))],
            ]);
        $participant = new Participe();
        $participant->PAR_plongee = $data['plongee'];
        $participant->PAR_adherent = $data['adherent'];
        $participant->save();
        if ($request->wantsJson())
            return Response()->json($participant);
        else
            return Redirect::back()->with(["result" => "Création réussie"]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Participe $participant
     * @return RedirectResponse
     */
    public function edit(Participe $participant): RedirectResponse
    {
        return Response()->redirectToRoute("/participants/".$participant->PAR_id."/edition");
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
        $participant = Participe::find($data['id']);
        return $this->doUpdate($data, $participant, $request);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request the request with data
     * @param Participe $participant the ship to update
     * @return JsonResponse|RedirectResponse
     * @throws ValidationException
     */
    public function updateWithId(Request $request, Participe $participant)
    {
        $id = $participant->PAR_id; // get id from the route
        $request->merge(["id"=>$id]); // in case it's not present in the body
        $data = $this->validateRequest($request, $id);
        return $this->doUpdate($data, $participant, $request);
    }

    /**
     * @param array $data
     * @param Participe $participant
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     * @throws Exception
     */
    public function doUpdate(array $data, Participe $participant, Request $request)
    {
        throw new Exception("No reason to use this");
        /*
        if (isset($data['plongee']))
            $participant->PAR_plongee = $data['plongee'];
        if (isset($data['adherent']))
            $participant->PAR_adherent = $data['adherent'];
        $participant->save();
        if ($request->wantsJson())
            return Response()->json($participant);
        else
            return Response()->redirectTo($request->session()->previousUrl())->with(["result" => "Création réussie"]);
        */
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param Participe $adherent
     * @return RedirectResponse|Response
     * @throws \Throwable if delete fails
     */
    public function destroy(Request $request, Participe $adherent)
    {
        $adherent->deleteOrFail();
        if ($request->wantsJson())
            return Response()->noContent(ResponseAlias::HTTP_OK);
        else
            return Response()->redirectTo($request->session()->previousUrl())->with(["result" => "Suppression réussie"]);
    }

    /**
     * @param Request $request
     * @param $id
     * @return array
     */
    public function validateRequest(Request $request, $id): array
    {
        return $request->validate([
            'id' => 'required|numeric|exists:PLO_PARTICIPE,PAR_id',
            'plongee' => 'required|numeric|exists:PLO_PLONGEES,PLO_id',
            'adherent' => ['required','numeric','exists:PLO_ADHERENTS,ADH_id',
                function ($attribute, $value, $fail) use ($request) {
                    /** @var Adherent $adherent */
                    $adherent = Adherent::with('niveau:NIV_id,NIV_niveau')->find($value);
                    /** @var Plongee $dive */
                    $dive = Plongee::with('niveau')->find($request->post('plongee'));
                    if ($adherent->niveau->NIV_niveau < $dive->niveau->NIV_niveau)
                        $fail('Le niveau du participant doit être au moins '.$dive->niveau->NIV_code);
                }]
        ]);
    }

}
