<?php

namespace App\Providers;

use App\Services\StaffDirectoryService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        View::composer('layouts.partials.navbar', function ($view): void {
            $view->with('pfNavbarProfile', app(StaffDirectoryService::class)->resolveUserProfile(auth()->user()));
        });
    }
}
