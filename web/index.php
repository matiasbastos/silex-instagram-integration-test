<?php

// to handle statics
if (array_key_exists('REQUEST_URI', $_SERVER) && preg_match('/\.(?:css|png|jpg|jpeg|gif)$/', $_SERVER['REQUEST_URI'])) {
    return false;
}

// autoload vendors
require_once __DIR__.'/../vendor/autoload.php';

// use
use MetzWeb\Instagram\Instagram;

            // lets create a silex app
$app = new Silex\Application();
// debug on
$app['debug'] = true;
// dependency injection: twig
$app->register(new Silex\Provider\TwigServiceProvider(), array('twig.path' => __DIR__.'/views'));
// the instagram api object of: https://github.com/cosenary/Instagram-PHP-API
$app['instagram'] = function () {
    return new Instagram(array(
        'apiKey' => 'b32840dfd1d64dd2becd20ddb86c7e98',
        'apiSecret' => '164067ddc96544acb66875202fe372a3',
        'apiCallback' => 'http://localhost:8080/redirecturi',
    ));
};

// this url gets the media info
$app->get('/media/{id}', function ($id) use ($app) {
    $instagram = $app['instagram'];
    $media = $instagram->getMedia($id);
    if ($media->meta->code != 200) {
        return $app->json($media, $media->meta->code);
    }

    return $app->json(['id' => $media->data->id, 'location' => ['geopoint' => $media->data->location]]);
});

// this url is an instagram login
$app->get('/', function () use ($app) {
    $instagram = $app['instagram'];
    $loginUrl = $instagram->getLoginUrl();

    return $app['twig']->render('login.twig', array('loginUrl' => $loginUrl));
});

// this url shows a gallery with the media of the logged user
$app->get('/redirecturi', function () use ($app) {
    $instagram = $app['instagram'];
    // receive OAuth code parameter
    $code = $_GET['code'];
    // check whether the user has granted access
    if (isset($code)) {
        // receive OAuth token object
        $data = $instagram->getOAuthToken($code);
        $username = $data->user->username;
        // store user access token
        $instagram->setAccessToken($data);
        try {
            // now you have access to all authenticated user methods
            $result = $instagram->getUserMedia();
        } catch (Exception $e) {
            return $app->redirect('/');
        }
    } else {
        // check whether an error occurred
        if (isset($_GET['error'])) {
            return 'An error occurred: '.$_GET['error_description'];
        }
    }
    $mediahtml = '';
    // display all user likes
    foreach ($result->data as $media) {
        $content = '<li>';
        // output media
        if ($media->type === 'video') {
            // video
            $poster = $media->images->low_resolution->url;
            $source = $media->videos->standard_resolution->url;
            $content .= "<video class=\"media video-js vjs-default-skin\" width=\"250\" height=\"250\" 
                         poster=\"{$poster}\" data-setup='{\"controls\":true, \"preload\": \"auto\"}'>
                         <source src=\"{$source}\" type=\"video/mp4\" />
                         </video>";
        } else {
            // image
            $image = $media->images->low_resolution->url;
            $content .= "<img class=\"media\" src=\"{$image}\"/>";
        }
        // create meta section
        $avatar = $media->user->profile_picture;
        $username = $media->user->username;
        $comment = $media->caption->text;
        $content .= "<div class=\"content\">
                       <div class=\"avatar\" style=\"background-image: url({$avatar})\"></div>
                       <p>{$username}</p>
                       <div class=\"comment\">{$comment}</div>
                     </div>";
        // debug media
        //$mediahtml .= "<xmp>".print_r($media, true)."</xmp>";
        // output media
        $mediahtml .= $content.'</li>';
    }
    $mediahtml = '<ul class="grid">'.$mediahtml.'</ul>';

    return $app['twig']->render('media.twig', array(
        'username' => $data->user->username,
        'media' => $mediahtml,
    ));
});

// run silex
$app->run();
