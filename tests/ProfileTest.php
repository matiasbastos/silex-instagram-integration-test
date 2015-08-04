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

    public function testInstagramSessionReset()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/');
        // Assert that the response status code is 2xx
        $this->assertTrue($client->getResponse()->isSuccessful());
        // Save session token
        $this->app['session']->set('token', $this->app['invalid.token']);
        // Save session control value
        $this->app['session']->set('token_control', $this->app['invalid.token']);
        // Assert that the token is deleted and the control token exists
        $client = $this->createClient();
        $crawler = $client->request('GET', '/');
        $this->assertFalse($this->app['session']->has('token'));
        $this->assertTrue($this->app['session']->has('token_control'));
    }

    public function testInstagramProfilePage()
    {
        // Save session token
        $this->app['session']->set('token', $this->app['valid.token']);
        // Get the profile page
        $client = $this->createClient();
        $crawler = $client->request('GET', '/profile');
        // Assert that the response status code is 2xx
        $this->assertTrue($client->getResponse()->isSuccessful());
        // Assert that the response content contains a valid title
        $this->assertContains("Instagram photos", $client->getResponse()->getContent());
        // Assert that there is at least one li tag
        $this->assertGreaterThan(0, $crawler->filter('li')->count());
        // Assert that the response content contains the valid media_id
        $this->assertContains($this->app['valid.media_id'], $client->getResponse()->getContent());
    }

    public function testInstagramProfilePageInvalidToken()
    {
        // Save session token
        $this->app['session']->set('token', $this->app['invalid.token']);
        // Get the profile page
        $client = $this->createClient();
        $crawler = $client->request('GET', '/profile');
        // Assert that the response is a redirect to /
        $this->assertTrue($client->getResponse()->isRedirect('/'));
    }
}
