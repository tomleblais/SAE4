<?php

namespace App\Http\Controllers;

use App\Mail\PasswordForgotten;
use App\Models\Adherent;
use App\Models\Personne;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use function PHPUnit\Framework\isNull;

class LoginController extends Controller
{
    /**
     * Handle an authentication attempt.
     *
     * @param Request $request
     * @return View|RedirectResponse|PasswordForgotten
     * @throws ValidationException
     */
    public function authenticate(Request $request)
    {
        $credentials = Validator::make($request->all(),[
            'email' => ['required', 'email'],
            'forgotten' => 'nullable',
            'pass' => ['required_without:forgotten'],
            'remember' => ['boolean']
        ], ['pass.required_without'=>"Le mot de passe est obligatoire pour se connecter."])->validate();
        if (isset($credentials['forgotten'])) {
            /**
             * @var Personne $person
            */
            $person = Personne::all()->where("PER_email", $credentials['email'])->first();
            if ($person != null) {
                $person->setRememberToken(md5(rand(1, 1000) . microtime()));
                return new PasswordForgotten($person); // TODO replace by next line for sending real mails
                //Mail::to($person->PER_email)->send(new PasswordForgotten($person));
            }
            return view("connection/mailSent");
        }
        if (! isset($credentials['remember']))
            $credentials['remember'] = false;
        if (Auth::attempt(['PER_email'=>$credentials['email'], 'password'=>$credentials['pass'], 'PER_active'=>1], $credentials['remember'])) {
            $request->session()->regenerate();
            return redirect()->intended('/accueil');
        }

        return back()->withInput($credentials)->withErrors([
            'email' => 'Identification échouée.',
        ]);
    }

    /**
     * Handle a password recovery attempt.
     *
     * @param Request $request
     * @param string $token
     * @param ?Personne $person
     * @return Application|Factory|RedirectResponse|View
     */
    public function recover(Request $request, string $token, ?Personne $person) {
        if (($person==null) || ($person->getRememberToken() != $token))
            return redirect()->route("login");
        Auth::login($person);
        session()->flashInput(['id'=>$person->PER_id, 'token'=>$token]);
        return \view("/people/changePassword");
    }
}
