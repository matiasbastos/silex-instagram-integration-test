# Silex/Instagram Integration Test

[![Build Status](https://travis-ci.org/matiasbastos/silex-instagram-integration-test.svg?branch=master)](https://travis-ci.org/matiasbastos/silex-instagram-integration-test)
[![License](https://img.shields.io/badge/license-MIT-lightgrey.svg)](https://github.com/SammyK/LaravelFacebookSdk/blob/master/LICENSE)

This is an integration test with Silex and Instagram API. It provides a json api to get media info from Instagram, an Instagram login page and a gallery page to see the logiged user media.

## Get started
The objective of the test was to provide a json api to get the location info of a media element from Instagram.
To build the project used the Symfony microframework: [Silex](https://github.com/silexphp/Silex) with [Mustache for PHP](https://github.com/bobthecow/mustache.php) and [Instagram PHP API](https://github.com/cosenary/Instagram-PHP-API) to made simpler the interaction with Instagram.
The project is fully unit-tested, compiles with the PSR-2 coding standar and uses TravisCI. Actually included a dev and a test config files for demo purpose only.

---

### TL;DR: How to run this project
To run this project just type this into the terminal:
```
$ git clone https://github.com/matiasbastos/silex-instagram-integration-test.git
$ cd silex-instagram-integration-test
$ composer self-update
$ composer install
$ php -S localhost:8080 -t web web/index.php
```

Now you can go to:
- [localhost:8080](http://localhost:8080/): Here you can see the Instagram login page. After give the permisions to your Instagram account you will be redirected to the [Profile Page](http://localhost:8080/profile).
- [localhost:8080/media/{your_media_id}](http://localhost:8080/media/1042328170164362082_2112310485):Here you can see the media location api. 

Here is an example of how the api works:
```
GET /media/1234567890
```

Response:
```
STATUS 200
{
   "id": 1234567890,
   "location": {
      "latitude": 12.3456,
      "longitude": -12.3456,
      "geocode": { ... }
   }
}
```

### Unit Testing
To run the tests, just go to the console, then go to the project root dir and run:
```
$ ./vendor/bin/phpunit  --configuration phpunit.xml
```

## Credits

[Matias Bastos](https://ar.linkedin.com/in/matiasbastos)
