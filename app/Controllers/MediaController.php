<?php
namespace Controllers;

use Silex\Application;
use Exception;

class MediaController
{
    public function getMediaLocation(Application $app, $id)
    {
        $instagram = $app['instagram'];
        try {
            $media = $instagram->getMedia($id);
            if ($media->meta->code != 200) {
                return $app->json($media, $media->meta->code);
            }
        } catch (Exception $ex) {
            return $app->json($ex, 500);
        }
        try {
            $geocode = $this->getGoogleGeocode(
                $media->data->location->latitude,
                $media->data->location->longitude,
                $app['geocodekey']
            );
            if ($geocode->status == "OK") {
                $geocode = $geocode->results;
            } else {
                throw new Exception("Somethin' wrong with google.");
            }
        } catch (Exception $ex) {
            $geocode = [];
        }

        return $app->json(['id' => $media->data->id,
                           'location' => ['geopoint' => $media->data->location,
                                          'geocode' => $geocode]
                          ]);
    }
    
    private function getGoogleGeocode($lat, $long, $key)
    {
        $ch = curl_init();
        curl_setopt(
            $ch,
            CURLOPT_URL,
            "https://maps.googleapis.com/maps/api/geocode/json?".
            "latlng={$lat},{$long}&result_type=street_address&key={$key}"
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        $data = json_decode($response);
        return $data;
    }

    public function getInstagramLogin(Application $app)
    {
        $instagram = $app['instagram'];
        $loginUrl = $instagram->getLoginUrl();

        return $app['twig']->render('login.twig', array('loginUrl' => $loginUrl));
    }

    public function showInstagramMedia(Application $app)
    {
        $instagram = $app['instagram'];
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
    }
}
