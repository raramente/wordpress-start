<?php
/**
 * A WordPress starter project using bedrock, sage, lando and deployer.
 * Deployer 8.x
 * php version 8.1
 * 
 * @category Deployer
 * @package WordPress
 * @author Hugo Silva <hugo@hugosilva.me>
 * @license https://opensource.org/license/mit The MIT License
 * @version GIT: @1.1.0@
 * @link https://github.com/raramente/wordpress-start
 * 
 */
namespace Deployer;

use Deployer\Exception\ConfigurationException;
use Symfony\Component\Yaml\Yaml;

import("setup.yaml");

localhost('local');


/**
 * Config tasks //TODO
 */
task('config:check', function() {
    if ( !has('name') ) {
        throw new ConfigurationException('Please define a name.');
    }
})->desc('Checks if all the configuration is set.');

/**
 * Download repo files for set up.
 */
task('setup:lando-download', function() {

    run('wget https://github.com/raramente/lando-start/archive/master.tar.gz');
    run('mkdir -p ./tmp');
    run('tar -zxvf master.tar.gz -C ./tmp');
    run('rm master.tar.gz');

    // Move lando files
    run('mv ./tmp/lando-start-master/.lando.base.yml ./.lando.base.yml');
    run('mv ./tmp/lando-start-master/.lando.yml ./.lando.yml');

    $wordpress_config = get('wordpress');
    // Create .env file.
    run('echo "# Wordpress set up" > .env.lando');
    run('echo "DB_NAME=\'' . $wordpress_config['db_name'] . '\'" >> .env.lando');
    run('echo "DB_USER=\'' . $wordpress_config['db_user'] . '\'" >> .env.lando');
    run('echo "DB_PASSWORD=\'' . $wordpress_config['db_password'] . '\'" >> .env.lando');
    run('echo "DB_HOST=\'' . $wordpress_config['db_host'] . '\'" >> .env.lando');
    run('echo "DB_PREFIX=\'' . $wordpress_config['db_prefix'] . '\'" >> .env.lando');
    run('echo \'\' >> .env.lando');
    run('echo "WP_ENV=\'development\'" >> .env.lando');
    run('echo "WP_HOME=\'{{public_url}}\'" >> .env.lando');
    run('echo "WP_SITEURL=\"\${WP_HOME}/wp\"" >> .env.lando');
    run('echo \'\' >> .env.lando');
    run('echo "AUTH_KEY=\'' . generateRandomString(64) . '\'" >>.env.lando');
    run('echo "SECURE_AUTH_KEY=\'' . generateRandomString(64) . '\'" >>.env.lando');
    run('echo "LOGGED_IN_KEY=\'' . generateRandomString(64) . '\'" >>.env.lando');
    run('echo "NONCE_KEY=\'' . generateRandomString(64) . '\'" >>.env.lando');
    run('echo "AUTH_SALT=\'' . generateRandomString(64) . '\'" >>.env.lando');
    run('echo "SECURE_AUTH_SALT=\'' . generateRandomString(64) . '\'" >>.env.lando');
    run('echo "LOGGED_IN_SALT=\'' . generateRandomString(64) . '\'" >>.env.lando');
    run('echo "NONCE_SALT=\'' . generateRandomString(64) . '\'" >>.env.lando');
    run('echo \'\' >> .env.lando');
    run('echo "# Theme Settings" >> .env.lando');
    run('echo "THEME_NAME=\'{{theme_name}}\'" >> .env.lando');

    // Create the .lando.yml file
    $lando_yaml = Yaml::parse(file_get_contents('.lando.yml'));
    $lando_yaml['name'] = get('name');
    $lando_yaml['proxy']['appserver_nginx'][0] = get('name') . '.lndo.site';
    $lando_yaml['proxy']['mailhog'][0] = 'mailhog.' . get('name') . '.lndo.site';
    $lando_yaml['proxy']['pma'][0] = 'pma.' . get('name') . '.lndo.site';
    file_put_contents('.lando.yml', Yaml::dump($lando_yaml));

    run('rm -rf ./tmp');
})->desc("Downloads lando starter project.");

task('setup:lando-start', function() {
    info('Starting lando');
    run('lando start');
})->desc('Starts lando');

task('setup:intall-wp', function() {
    run('lando install-bedrock');
    run('lando create-env');

    $wordpress_config = get('wordpress');
    run('lando wp core install --skip-email --url={{public_url}} --title="{{display_name}}" --admin_email=' . $wordpress_config['admin_email'] . ' --admin_user=' . $wordpress_config['admin_user'] . ' --admin_password=' . $wordpress_config['admin_password'] . ' --locale=' . $wordpress_config['locale']);
})->desc('Starts lando');

task('setup:install-theme', function() {
    run('lando install-sage');
    run('lando theme-build');
})->desc('Installs and builds the theme.');

task('setup:load-gitignore', function() {
    run('cp ./helper-files/.gitignore.root .gitignore');
    run('cp ./helper-files/.gitignore.bedrock ./{{path_to_wp_root}}/.gitignore');
    run('cp ./helper-files/.gitignore.sage ./{{path_to_wp_root}}/web/app/themes/{{theme_name}}/.gitignore');
})->desc('Load .gitignore(s)');

task('setup:deployer', function() {

    run('wget https://github.com/raramente/deployer-start/archive/master.tar.gz');
    run('mkdir -p ./tmp');
    run('tar -zxvf master.tar.gz -C ./tmp');
    run('rm master.tar.gz');

    // Move lando files
    run('mv ./tmp/deployer-start-master/deploy-config.yaml ./deploy-config.yaml');

    // Create the .lando.yml file
    $deployer_yaml = Yaml::parse(file_get_contents('deploy-config.yaml'));
    $deployer_yaml['config']['name'] = get('display_name');
    $deployer_yaml['config']['repository'] = get('repository');
    $deployer_yaml['config']['theme/name'] = get('theme_name');
    $deployer_yaml['config']['local/address'] = get('public_url');
    $deployer_yaml['config']['local/path_to_wp_root'] = get('path_to_wp_root');
    $deployer_yaml['config']['notifications']['enabled'] = false;
    file_put_contents('deploy-config.yaml', Yaml::dump($deployer_yaml, 4));

    warning("Please edit your hosts and hangouts webhook on deploy-config.yaml.");
    

    // TODO
    # run('mv ./tmp/deployer-start-master/deploy.php ./deploy.php');

})->desc('Sets up deployer.');

task('setup:cleanup', function() {
    // TODO
})->desc('Cleans up left over files.');

//TODO - Remove this file for deployer.

/**
 * Set up stuff
 */
task('setup', [
    'config:check',
    'setup:lando-download',
    'setup:lando-start',
    'setup:intall-wp',
    'setup:install-theme',
    'setup:load-gitignore',
    'setup:cleanup'
])->desc('Sets up everything on local environment.');

/**
 * Helper functions
 */
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';

    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
    }

    return $randomString;
}
