# Olapic Test

[![Build Status](https://travis-ci.org/matiasbastos/olapic-test.svg?branch=master)](https://travis-ci.org/matiasbastos/olapic-test)

This is an Olapic Test made to create a json api to get media info from Instagram.

## Get started
The objective of the test was to provide a json api to get the location info of a media element from Instagram.
To build the project decided to use Silex (as sugested) with Twig and [Instagram PHP API](https://github.com/cosenary/Instagram-PHP-API) to made simpler the interaction with Instagram.
Created a test app with my Instagram account to be able to interact with Instagram. 
Is the first time I use the Instagram API and also the first time I use Silex, so decided to add more features just to play a bit and learn. 
So if u enter to the root url ('/') you will see an Instragram login page, and after login, you will be redirected to a gallery that show your media on your Instagram account.

---

### How to run this project
To run this project just type this into the terminal:
```
$ git clone https://github.com/matiasbastos/olapic-test.git
$ cd olapic-test
$ php -S localhost:8080 -t web web/index.php
```
Then you can write in your browser 'localhost:8080/media/1039923172635336043_47787070', for example,  to test the media api. Or go to 'localhost:8080' to see the Instagram login.

### Unit Testing
To run the tests, you will need to have [PHPUnit](https://phpunit.de/manual/3.7/en/installation.html) installed. Then in the console go to the project root dir and run:
```
$ phpunit
```

## Credits

[Matias Bastos](https://ar.linkedin.com/in/matiasbastos)
