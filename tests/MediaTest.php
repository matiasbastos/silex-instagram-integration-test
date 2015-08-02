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

    public function testMedia()
    {
        $client = $this->createClient();
        // test a known image 
        $crawler = $client->request('GET', '/media/1039923172635336043_47787070');
        $this->assertTrue($client->getResponse()->isOk());
        // test a not existing image 
        $crawler = $client->request('GET', '/media/1039923172635336043_47787070a');
        $this->assertTrue($client->getResponse()->getStatusCode()==400);
    }
}
