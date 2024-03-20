<?php

namespace App\Providers;

use App\Models\Personne;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define("secretaire", function (Personne $person){
            return $person->isSecretary() || $person->isDirector();
        });
        Gate::define('directeur-section', function (Personne $person) {
            return $person->isDirector();
        });
        Gate::define('delete', function ($user) {
            return $user->isDirector() || $user->isSecretary();
        });
        
        Gate::define('put', function ($user) {
            return $user->isDirector() || $user->isSecretary();
        });
    }
}
