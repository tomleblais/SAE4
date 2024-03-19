<?php

namespace App\Providers;

use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        try {
            $res = DB::select("SELECT count(*) as nb FROM PLO_LIEUX");
            if ($res[0]->nb == 0) throw new Exception("No site found.");
            //Log::info('No migration needed');
        } catch (Throwable $exception) {
            Log::debug($exception->getMessage());
            Log::info('Running migrations');
            Artisan::call("migrate:fresh");
        }
        //
        /* For debugging Eloquent requests */
        if (false)
            DB::listen(function ($query) {
                Log::info("SQL=".$query->sql);
                Log::info("BINDINGS=".print_r($query->bindings,true));
            });
    }
}
