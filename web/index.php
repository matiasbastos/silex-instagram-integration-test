<?php

/**
 * This the app initialization for Silex.
 *
 * First it handles the statics request to be able to execute this app
 * using the PHP internal web server.
 * Then it calls the autoload.php from the vendors dir and after that
 * it configures the app object including: SessionServiceProvider,
 * ConfigServiceProvider, Mustache_Engine and cosenary/instagram api helper.
 * Finally it declares the user urls.
 *
 * This app can be executed with the following commands:
 * <samp>
 * $ composer install
 * $ php -S localhost:8080 -t web web/index.php
 * </samp>
 *
 * (c) Matias Bastos <matias.bastos@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use MetzWeb\Instagram\Instagram;
use Controllers\MediaApiController;
use Controllers\ProfileController;

/**
 * handle static requests
 */
if (array_key_exists('REQUEST_URI', $_SERVER) &&
    preg_match('/\.(?:html|js|css|png|jpg|jpeg|gif|woff|ttf)$/', $_SERVER['REQUEST_URI'])) {
    return false;
}

require_once __DIR__.'/../vendor/autoload.php';

/**
 * create and config Silex App
 */
$app = new Silex\Application();
$app->register(new Silex\Provider\SessionServiceProvider());
if (!isset($env)) {
    $env = 'dev';
}
$app->register(new Igorw\Silex\ConfigServiceProvider(__DIR__."/../config/config.{$env}.json"));
$app['mustache'] = new Mustache_Engine(
    [
    'loader' => new Mustache_Loader_FilesystemLoader(__DIR__.'/../app/Views', ['extension' => '.html']),
    ]
);
$app['instagram'] = function () use ($app) {
    return new Instagram(
        array(
        'apiKey' => $app['instagram_api']['apiKey'],
        'apiSecret' => $app['instagram_api']['apiSecret'],
        'apiCallback' => $app['instagram_api']['apiCallback'],
        )
    );
};

/**
 * This url gets the media_id as parameter and it returns the location data in
 * json format.
 */
$app->get('/media/{id}', "Controllers\MediaApiController::getMediaLocation");

/**
 * This url returns the instagram login page.
 */
$app->get('/', "Controllers\ProfileController::showInstagramLogin");

/**
 * This url shows a gallery with the media of the logged user.
 */
$app->get('/profile', "Controllers\ProfileController::showInstagramMedia");

/*
 * Run Silex.
 */
$app->run();
