<?php
use MetzWeb\Instagram\Instagram;
use Controllers\MediaController;

// to handle statics
if (array_key_exists('REQUEST_URI', $_SERVER) && preg_match('/\.(?:css|png|jpg|jpeg|gif)$/', $_SERVER['REQUEST_URI'])) {
    return false;
}

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();
$app['debug'] = true;
$app->register(new Silex\Provider\TwigServiceProvider(), array('twig.path' => __DIR__.'/../app/Views'));
$app->register(new Igorw\Silex\ConfigServiceProvider(__DIR__."/../config/config.json"));
$app['instagram'] = function () use ($app) {
    return new Instagram(array(
        'apiKey' => $app['instagram_api']['apiKey'],
        'apiSecret' => $app['instagram_api']['apiSecret'],
        'apiCallback' => $app['instagram_api']['apiCallback'],
    ));
};

// this url gets the media info
$app->get('/media/{id}', "Controllers\MediaController::getMediaLocation");

// this url is an instagram login
$app->get('/', "Controllers\MediaController::getInstagramLogin");

// this url shows a gallery with the media of the logged user
$app->get('/profile', "Controllers\MediaController::showInstagramMedia");

// run silex
$app->run();
