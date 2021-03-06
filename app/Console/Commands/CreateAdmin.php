<?php

namespace App\Console\Commands;

use App\Role;
use App\User;
use Config;
use URL;
use App\PasswordReset;
use Illuminate\Console\Command;

class CreateAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'projects:createadmin {username} {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a site admin user';

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
        $user = User::create([
            'username' => $this->argument('username'),
            'email' => $this->argument('email'),
            'password' => bcrypt(str_random(40)),
            'is_admin' => true,
        ]);
        $token = PasswordReset::create([
            'user_id' => $user->id,
            'token' => strtolower(str_random(64)),
        ]);
        $url = route('password.reset', ['token' => $token->token]);
        $this->info('Now go to ' . $url);
    }
}
