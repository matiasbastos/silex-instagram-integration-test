<?php
namespace Controllers;

use Silex\Application;

class MediaController
{
    public function getMediaLocation(Application $app, $id)
    {
        $instagram = $app['instagram'];
        $media = $instagram->getMedia($id);
        if ($media->meta->code != 200) {
            return $app->json($media, $media->meta->code);
        }

        return $app->json(['id' => $media->data->id, 'location' => ['geopoint' => $media->data->location]]);
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
