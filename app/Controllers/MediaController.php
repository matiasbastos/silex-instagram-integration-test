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
}
