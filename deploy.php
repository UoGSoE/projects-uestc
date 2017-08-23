<?php
namespace Deployer;

require 'recipe/laravel.php';

// Project name
set('application', 'UESTCprojects');

// Project repository
set('repository', 'git@versions.eng.gla.ac.uk:billy/projects2.git');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true);

// Shared files/dirs between deploys
add('shared_files', []);
add('shared_dirs', []);

// Writable dirs by web server
add('writable_dirs', []);


// Hosts

host('projects.eng.gla.ac.uk')
    ->user('deployer')
    ->set('deploy_path', '/opt/rh/httpd24/root/var/www/html/uestc2017');
// Tasks

task('build', function () {
    run('cd {{release_path}} && build');
});

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Migrate database before symlink new release.

before('deploy:symlink', 'artisan:migrate');
