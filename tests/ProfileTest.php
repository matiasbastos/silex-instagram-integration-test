<?php
use Silex\WebTestCase;
use Silex\Application;

class ProfileTest extends WebTestCase
{
    public function createApplication()
    {
        date_default_timezone_set("America/Argentina/Cordoba");
        $env = "test";
        require __DIR__.'/../web/index.php';
        unset($app['exception_handler']);
        return $app;
    }

    public function testInstagramLoginPage()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/');
        // Assert that the response status code is 2xx
        $this->assertTrue($client->getResponse()->isSuccessful());
        // Assert that the response content contains a valid login url
        $this->assertContains($this->app['valid.login_url'], $client->getResponse()->getContent());
    }
}
