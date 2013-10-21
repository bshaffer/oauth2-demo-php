<?php

require_once __DIR__.'/../vendor/autoload.php';

/** show all errors! */
ini_set('display_errors', 1);
error_reporting(E_ALL);

/** set up the silex application object */
$app = new Silex\Application();
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../views',
));

$app['debug'] = true;

/** set up routes / controllers */
$app->mount('/', new OAuth2Demo\Client\Client());
$app->mount('/lockdin', new OAuth2Demo\Server\Server());

// create an http foundation request implementing OAuth2\RequestInterface
$request = OAuth2\HttpFoundationBridge\Request::createFromGlobals();
$app->run($request);
