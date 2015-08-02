<?php
namespace Controllers;

use Silex\Application;
use Exception;

class MediaController
{
    public function getMediaLocation(Application $app, $id)
    {
        $instagram = $app['instagram'];
        $location = [];
        // get media from instagram
        try {
            $media = $instagram->getMedia($id);
            if ($media->meta->code != 200) {
                return $app->json($media, $media->meta->code);
            }
        } catch (Exception $ex) {
            return $app->json($ex, 500);
        }
        // check for location info from instagram
        try {
            $location['latitude'] = $media->data->location->latitude;
            $location['longitude'] = $media->data->location->longitude;
            if(!$location['latitude'] || !$location['longitude']){
                throw new Exception("No instagram location data.");
            }    
        } catch (Exception $ex) {
            return $app->json(['id' => $media->data->id, 'location' => 'no location data']);
        }    
        // get aditional location info from google
        try {
            $geocode = $this->getGoogleGeocode(
                $location['latitude'],
                $location['longitude'],
                $app['google_api']['apiKey']
            );
            if ($geocode->status == "OK") {
                $location['geocode'] = $geocode->results;
            } else {
                throw new Exception("Somethin' wrong with google.");
            }
        } catch (Exception $ex) {
            $location['geocode'] = "no geocode info";
        }

        return $app->json(['id' => $media->data->id, 'location' => $location]);
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

    public function showInstagramLogin(Application $app)
    {
        $app['session']->remove('token');
        return $app['mustache']->render('login', ['loginUrl'=>$app['instagram']->getLoginUrl()]);
    }

    public function showInstagramMedia(Application $app)
    {
        $instagram = $app['instagram'];
        // check whether an error occurred
        if (isset($_GET['error'])) {
            return 'An error occurred: '.$_GET['error_description'];
        }
        // check whether the user has granted access
        if (isset($_GET['code'])) {
            // receive OAuth token object
            $t = $instagram->getOAuthToken($_GET['code'], true);
            $app['session']->set('token', $t);
            return $app->redirect('/profile');
        }
        try {
            // get user token
            $token = $app['session']->get('token');
            // store user access token
            $instagram->setAccessToken($token);
            // get user data
            $user = $instagram->getUser();
            // now you have access to all authenticated user methods
            $result = $instagram->getUserMedia();
        } catch (Exception $e) {
            return $app->redirect('/');
        }
        // build user media array
        $media_data = [];
        foreach ($result->data as $media) {
            $m = [];
            if ($media->type === 'video') {
                $m['video'] = [
                    'poster'=> $media->images->low_resolution->url,
                    'source'=> $media->videos->standard_resolution->url
                ];
            } else {
                $m['image'] = ['source' => $media->images->low_resolution->url];
            }
            $m['meta'] = [
                'id' => $media->id,
                'avatar' => $media->user->profile_picture,
                'username' => $media->user->username,
                'comment' => $media->caption->text
            ];
            $media_data[] = $m;
            //echo "<xmp>".print_r($media,true)."</xmp>";
        }

        return $app['mustache']->render('media_gallery', array(
            'username' => $user->data->username,
            'media' => $media_data,
        ));
    }
}
