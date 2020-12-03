<?php
namespace Deployer;

require 'recipe/symfony.php';

// Project name
set('application', 'EVEAuth');

// Project repository
set('repository', 'git@github.com:nicti/eveauth.git');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true); 

// Shared files/dirs between deploys 
add('shared_files', []);
add('shared_dirs', []);

// Writable dirs by web server 
add('writable_dirs', []);
set('allow_anonymous_stats', false);

// Hosts

host('eveauth.nicti.de')
    ->user('nicti')
    ->identityFile('~/.ssh/id_rsa')
    ->hostname('mail-srv.nicti.de')
    ->set('deploy_path', '~/www/{{application}}');
    
// Tasks

task('build', function () {
    run('cd {{release_path}} && build');
});

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Migrate database before symlink new release.

before('deploy:symlink', 'database:migrate');

task('pwd', function() {
    $result = run('pwd');
    writeln("Current dir: $result");
});