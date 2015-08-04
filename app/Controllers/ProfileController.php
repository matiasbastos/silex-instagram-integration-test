<?php
/**
 *This file contains the ProfileController Class.
 */
namespace Controllers;

use Silex\Application;
use Exception;

/**
 * ProfileController
 *
 * This class contains the logic for the sample Instagram login and
 * a sample of a media gallery to show the loged user media.
 *
 * @author  Matias Bastos <matias.bastos@gmail.com>
 * @license http://www.opensource.org/licenses/MIT The MIT License
 */
 
class ProfileController
{
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
