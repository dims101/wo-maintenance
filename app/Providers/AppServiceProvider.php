<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('access-by-role-dept', function ($user, $roles = null, $depts = null) {

            $rolesArray = $roles ? explode(',', $roles) : [];
            $deptsArray = $depts ? explode(',', $depts) : [];

            $roleAllowed = empty($rolesArray) || in_array($user->role_id, $rolesArray);
            $deptAllowed = empty($deptsArray) || in_array($user->dept_id, $deptsArray);

            return $roleAllowed && $deptAllowed;
        });

        config(['app.locale' => 'id']);
        Carbon::setLocale('id');
        date_default_timezone_set('Asia/Jakarta');
    }
}
