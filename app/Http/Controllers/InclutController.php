<?php

namespace App\Http\Controllers;

use App\Models\Adherent;
use App\Models\Inclut;
use App\Models\Palanquee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Throwable;

class InclutController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return Response()->json(Inclut::all());
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function indexWithDetails(): JsonResponse
    {
        $all = Inclut::with(['adherent.niveau:NIV_id,NIV_code', 'palanquee.plongee.niveau:NIV_id,NIV_code'])->get();
        return Response()->json($all);
    }

    /**
     * Display the specified resource.
     *
     * @param Inclut $participant
     * @return JsonResponse
     */
    public function show(Inclut $participant): JsonResponse
    {
        return Response()->json($participant);
    }

    /**
     * Display the specified resource.
     *
     * @param Inclut $member
     * @return JsonResponse
     */
    public function showWithDetails(Inclut $member): JsonResponse
    {
        $details = $member->load(['adherent.niveau:NIV_id,NIV_code', 'palanquee.plongee.niveau:NIV_id,NIV_code']);
        return Response()->json($details);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @param Palanquee $palanquee
     * @return JsonResponse|RedirectResponse
     */
    public function store(Request $request, Palanquee $palanquee)
    {
        $PAL_id = $palanquee->PAL_id;
        $PLO_id = $palanquee->PAL_plongee;
        $data = $request->validate([
            'adherent' => ['required','numeric','exists:PLO_ADHERENTS,ADH_id', 'bail',
                Rule::exists('PLO_PARTICIPE', 'PAR_adherent')->where('PAR_plongee', $PLO_id),
                Rule::unique('PLO_INCLUT','INC_adherent')->where('INC_palanquee', $PAL_id),
                function ($attribute, $value, $fail) use ($request,$palanquee) {
                    /** @var Adherent $adherent */
                    $adherent = Adherent::with('niveau')->find($value);
                    $dive = $palanquee->load("plongee.niveau")->plongee;
                    $adh_niv = $adherent->niveau->NIV_niveau;
                    $dive_niv = $dive->niveau->NIV_niveau;
                    if ($adh_niv < $dive_niv)
                        $fail('Le niveau du membre doit être au moins '.$dive->niveau->NIV_code);
                },
                function ($attribute, $value, $fail) use ($request,$PLO_id) {
                    if (! empty(DB::select("SELECT INC_id FROM PLO_INCLUT
                     JOIN PLO_PALANQUEES ON INC_palanquee=PAL_id
                     WHERE PAL_plongee=:plongee AND INC_adherent=:adherent",
                        ['plongee'=>$PLO_id, 'adherent'=>$value])))
                        $fail("L'adhérent est déjà dans une palanquée.");
                }
            ]
        ], ['adherent.exists'=>"L'adhérent ne participe pas à cette plongée.",
            'adherent.unique'=>"L'adhérent est déjà dans la palanquée."]);
        $member = new Inclut();
        $member->INC_palanquee = $PAL_id;
        $member->INC_adherent = $data['adherent'];
        $member->save();
        if ($request->wantsJson())
            return Response()->json($member);
        else
            return Response()->redirectTo($request->session()->previousUrl())->with(["result" => "Création réussie"]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param Inclut $member
     * @return RedirectResponse|Response
     * @throws Throwable if delete fails
     */
    public function destroy(Request $request, Inclut $member)
    {
        $member->deleteOrFail();
        if ($request->wantsJson())
            return Response()->noContent(ResponseAlias::HTTP_OK);
        else
            return Response()->redirectTo($request->session()->previousUrl())->with(["result" => "Suppression réussie"]);
    }

    public function update(Request $request, Inclut $member) {
        $ADH_id = $member->INC_adherent;
        $data = $request->validate([
            'palanquee' => ['required', 'numeric', 'bail', 'exists:PLO_PALANQUEES,PAL_id',
                Rule::unique('PLO_INCLUT','INC_palanquee')->where('INC_adherent', $ADH_id)
                ->ignore($member->INC_id, 'INC_id'),
                function($attribute, $value, $fail) use ($member) {
                    $oldDive = $member->palanquee->PAL_plongee;
                    $newPal = Palanquee::find($value);
                    if ($newPal->PAL_plongee != $oldDive)
                        $fail("La modification de palanquée n'est valide qu'au sein d'une même plongée.");
                }
            ]
        ]);
        $member->INC_palanquee = $data['palanquee'];
        $member->save();
        if ($request->wantsJson())
            return Response()->json($member);
        else
            return Response()->redirectTo($request->session()->previousUrl())->with(["result" => "Édition réussie"]);
    }
}
