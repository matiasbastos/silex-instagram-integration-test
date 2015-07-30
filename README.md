# Olapic Test
This is an Olapic Test made to create a json api to get media info from Instagram.
## Get started
The objective of the test was to provide a json api to get the location info of a media element from Instagram.
To build the project decided to use Silex (as sugested) with Twig and [Instagram PHP API](https://github.com/cosenary/Instagram-PHP-API) to made simpler the interaction with Instagram.
Created a test app with my Instagram account to be able to interact with Instagram.
Is the first time I use the Instagram API and also the first time I use Silex, so decided to add more features just to play a bit and learn.
So if u enter to the root url ("/") you will se an Instragram login page, and after the login, you will be redirected to a gallery that show your media on your Instagram account.
---
### How to run this project
To run this project just type this into the terminal:
```
$ git clone https://github.com/matiasbastos/olapic-test.git
$ cd olapic-test
$ php -S localhost:8080 -t web web/index.php
```
