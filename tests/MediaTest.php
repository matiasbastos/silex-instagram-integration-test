<?php
require __DIR__ . '/../vendor/autoload.php';
use Silex\WebTestCase;
use Silex\Application;

class MediaTest extends WebTestCase
{
    public function createApplication()
    {
        $app = new Application();
        $app['debug'] = true;
        unset($app['exception_handler']);
        require __DIR__.'/../web/index.php';
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
