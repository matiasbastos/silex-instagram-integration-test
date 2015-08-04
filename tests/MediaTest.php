<?php
use Silex\WebTestCase;
use Silex\Application;

class MediaTest extends WebTestCase
{
    public function createApplication()
    {
        date_default_timezone_set("America/Argentina/Cordoba");
        $env = "test";
        require __DIR__.'/../web/index.php';
        unset($app['exception_handler']);
        return $app;
    }

    public function testValid()
    {
        $client = $this->createClient();
        // test a valid image 
        $crawler = $client->request('GET', '/media/'.$this->app['valid.media_id']);
        // Assert that the "Content-Type" header is "application/json"
        $this->assertTrue(
            $client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );
        // Assert that the response status code is 2xx
        $this->assertTrue($client->getResponse()->isSuccessful());
        // Assert that the response content contains the right latitude
        $this->assertContains(
            '"latitude":'.$this->app['valid.latitude'],
            $client->getResponse()->getContent()
        );
        // Assert that the response content contains the right longitude
        $this->assertContains(
            '"longitude":'.$this->app['valid.longitude'],
            $client->getResponse()->getContent()
        );
        // Assert that the response content contains the right google place_id
        $this->assertContains(
            '"place_id":"'.$this->app['valid.place_id'].'"',
            $client->getResponse()->getContent()
        );
    }

    public function testWrongUrl()
    {
        $client = $this->createClient();
        // test wrong url
        $crawler = $client->request('GET', '/media/');
        // Assert that the response status code is 404
        $this->assertTrue($client->getResponse()->isNotFound());
    }

    public function testInvalidMediaId()
    {
        $client = $this->createClient();
        // test an invalid image 
        $crawler = $client->request('GET', '/media/'.$this->app['invalid.media_id']);
        // Assert that the "Content-Type" header is "application/json"
        $this->assertTrue(
            $client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );
        // Assert that the response content contains error code 400
        $this->assertContains('APINotFoundError', $client->getResponse()->getContent());
        // Assert that the response status code is 400
        $this->assertEquals(
            400,
            $client->getResponse()->getStatusCode()
        );
    }

    public function testPrivateMediaId()
    {
        $client = $this->createClient();
        // test an invalid image 
        $crawler = $client->request('GET', '/media/'.$this->app['invalid.private_media_id']);
        // Assert that the "Content-Type" header is "application/json"
        $this->assertTrue(
            $client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );
        // Assert that the response content contains error code 400
        $this->assertContains('APINotAllowedError', $client->getResponse()->getContent());
        // Assert that the response status code is 400
        $this->assertEquals(
            400,
            $client->getResponse()->getStatusCode()
        );
    }

    public function testNoLocationMediaId()
    {
        $client = $this->createClient();
        // test an invalid image 
        $crawler = $client->request('GET', '/media/'.$this->app['invalid.no_location_media_id']);
        // Assert that the "Content-Type" header is "application/json"
        $this->assertTrue(
            $client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );
        // Assert that the response content contains error code 400
        $this->assertContains('no location data', $client->getResponse()->getContent());
        // Assert that the response status code is 2xx
        $this->assertTrue($client->getResponse()->isSuccessful());
    }
}
