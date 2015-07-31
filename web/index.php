<?php

// to handle statics
if (array_key_exists('REQUEST_URI', $_SERVER) && preg_match('/\.(?:css|png|jpg|jpeg|gif)$/', $_SERVER['REQUEST_URI'])) {
    return false;
}

// autoload vendors
require_once __DIR__.'/../vendor/autoload.php';

// use
use MetzWeb\Instagram\Instagram;
use Controllers\MediaController;

// lets create a silex app
$app = new Silex\Application();
// debug on
$app['debug'] = true;
// dependency injection: twig
$app->register(new Silex\Provider\TwigServiceProvider(), array('twig.path' => __DIR__.'/../app/Views'));
// the instagram api object of: https://github.com/cosenary/Instagram-PHP-API
$app['instagram'] = function () {
    return new Instagram(array(
        'apiKey' => 'b32840dfd1d64dd2becd20ddb86c7e98',
        'apiSecret' => '164067ddc96544acb66875202fe372a3',
        'apiCallback' => 'http://localhost:8080/profile',
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
