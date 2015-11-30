<?php

namespace App\Providers;

use App\Permission;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
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
    public function boot(GateContract $gate)
    {
        $this->registerPolicies($gate);

        foreach ($this->getPermissions() as $permission) {
            $gate->define($permission->title, function ($user) use ($permission) {
                return $user->hasRole($permission->roles);
            });
        }

        $gate->define('edit_this_project', function ($user, $project) {
            if ($user->hasRole('teaching_office')) {
                return true;
            }
            if ($user->hasRole('site_admin')) {
                return true;
            }
            return $user->id == $project->user_id;
        });

        $gate->define('view_this_project', function ($user, $project) {
            if ($user->hasRole('teaching_office')) {
                return true;
            }
            if ($user->hasRole('site_admin')) {
                return true;
            }
            if ($user->hasRole('convenor')) {
                return true;
            }
            return $user->id == $project->user_id;
        });
    }

    protected function getPermissions()
    {
        return Permission::with('roles')->get();
    }
}
