<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Storage;

class AllowApplications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'projects:allowapplications {flag : yes or no}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Toggle whether students can apply for projects';

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
        $flag = $this->argument('flag');
        if ($flag != 'yes' and $flag != 'no') {
            $this->error('Flag argument must be "yes" or "no"');
            abort();
        }
        if ($flag == 'yes') {
            Storage::delete('projects.disabled');
        }
        if ($flag == 'no') {
            Storage::put('projects.disabled', '');
        }
    }
}
