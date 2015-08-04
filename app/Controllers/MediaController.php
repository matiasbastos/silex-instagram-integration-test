<?php
namespace Controllers;

use Silex\Application;
use Exception;

/**
 * MediaController
 *
 * This file contains the logic for the media api to get the location data
 * of an Instagram media and also contains a sample Instagram login and
 * a sample of a media gallery to show the loged user media.
 *
 * @author  Matias Bastos <matias.bastos@gmail.com>
 * @license http://www.opensource.org/licenses/MIT The MIT License
 */
 
class MediaController
{
    /**
     * This function is used to get the Instagram media location data
     *
     * First obtains the lat and long data from Instagram and then
     * it uses the the geocode api from Google to get aditional
     * information from those coords.
     *
     * Here is an example of a call to this function
     * <samp>
     * GET /media/1234567890
     * </samp>
     *
     * Response:
     * <samp>
     * STATUS 200
     * {
     *    "id": 1234567890,
     *    "location": {
     *       "latitude": 12.3456,
     *       "longitude": -12.3456,
     *       "geocode": { ... }
     *    }
     * }
     * </samp>
     *
     * @param object $app the silex application object.
     * @param int    $id  the instagram media id.
     *
     * @return string the json response with the media id and the
     *                location data for the given media id.
     *
     * @access public
     */
    public function getMediaLocation(Application $app, $id)
    {
        $instagram = $app['instagram'];
        $location = [];
        /**
         * get media from instagram
         */
        try {
            $media = $instagram->getMedia($id);
            if ($media->meta->code != 200) {
                return $app->json($media, $media->meta->code);
            }
        } catch (Exception $ex) {
            return $app->json($ex, 500);
        }
            /**
             * check for location info from instagram
             */
        try {
            $location['latitude'] = $media->data->location->latitude;
            $location['longitude'] = $media->data->location->longitude;
            if (!$location['latitude'] || !$location['longitude']) {
                throw new Exception("No instagram location data.");
            }
        } catch (Exception $ex) {
            return $app->json(['id' => $media->data->id, 'location' => 'no location data']);
        }
        /**
         * get aditional location info from google
         */
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
    
    /**
     * This function is used to get the geocode data from Google.
     *
     * This function uses cURL to get the json data from google
     * and it returns an object with the results.
     *
     * @param string $lat  the latitude position.
     * @param string $long the longitude position.
     * @param string $long the google geocode api key.
     *
     * @return object the google geocode response.
     *
     * @access private
     */
    private function getGoogleGeocode($lat, $long, $key)
    {
        try {
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
        } catch (Exception $ex) {
            return $ex;
        }    
        return $data;
    }

    /**
     * This function renders the Instagam login page.
     *
     * It uses the instagram api to get the login url and uses
     * mustache to render the view.
     *
     * @param object $app the silex application object.
     *
     * @return string the html response.
     *
     * @access public
     */
    public function showInstagramLogin(Application $app)
    {
        $app['session']->remove('token');
        return $app['mustache']->render('login', ['loginUrl'=>$app['instagram']->getLoginUrl()]);
    }

    /**
     * This function renders the media gallery page.
     *
     * First it handles the callback from the Instagram login.
     * With the code parameter that comes via get it tries to get
     * the Instagram OAuth token, then is stored in the session
     * and it redirects the user to the /profile url with out the
     * login code.
     * After that it uses the token stored in the session to
     * get the user's media  and builds an array with the data to
     * render the template using mustache to render the view.
     * Also handles the errors messages sent via get.
     *
     * @param object $app the silex application object.
     *
     * @return string the html response.
     *
     * @access public
     */
    public function showInstagramMedia(Application $app)
    {
        $instagram = $app['instagram'];
        /**
         * check whether an error occurred
         */
        if (isset($_GET['error'])) {
            return 'An error occurred: '.$_GET['error_description'];
        }
        /**
         * check whether the user has granted access
         */
        if (isset($_GET['code'])) {
            try {
                $t = $instagram->getOAuthToken($_GET['code'], true);
                $app['session']->set('token', $t);
                return $app->redirect('/profile');
            } catch (Exception $e) {
                return $app->redirect('/profile?error='.$e->getMessage());
            }
        }
        try {
            $token = $app['session']->get('token');
            $instagram->setAccessToken($token);
            $user = $instagram->getUser();
            if ($user->meta->code != 200) {
                throw new Exception("OAuth error.");
            }
            $result = $instagram->getUserMedia();
        } catch (Exception $e) {
            return $app->redirect('/');
        }
        /**
         * build user media array
         */
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
