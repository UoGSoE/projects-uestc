<?php

namespace App\Providers;

use App\Permission;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any application authentication / authorization services.
     *
     * @param  \Illuminate\Contracts\Auth\Access\Gate  $gate
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('edit_users', function ($user) {
            return $user->isAdmin();
        });
        
        Gate::define('edit_this_project', function ($user, $project) {
            if ($user->isAdmin()) {
                return true;
            }
            return $user->id == $project->user_id;
        });

        Gate::define('view_this_project', function ($user, $project) {
            if ($user->isAdmin()) {
                return true;
            }
            return $user->id == $project->user_id;
        });
    }
}
