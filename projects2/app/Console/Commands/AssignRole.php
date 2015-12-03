<?php

namespace App\Console\Commands;

use App\Role;
use App\User;
use Illuminate\Console\Command;

class AssignRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'projects:assignrole {role} {username}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $role = Role::where('title', '=', $this->argument('role'))->first();
        if (!$role) {
            $this->error('No such role title');
            exit(-1);
        }
        $user = User::where('username', '=', $this->argument('username'))->first();
        if (!$user) {
            $this->error('No such username');
            exit(-2);
        }
        $user->AssignRole($role);
        $this->info($user->fullName() . ' now has role ' . $role->label);
    }
}
