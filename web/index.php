<?php

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../views',
));
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app['debug'] = true;

/* set up dependency injection container */
$app['oauth_storage'] = function ($app) {
    return new OAuth2_Storage_Pdo(array('dsn' => 'sqlite://'.__DIR__.'/../data/oauth.sqlite'));
};

$app['oauth_server'] = function($app) {
    $server = new OAuth2_Server($app['oauth_storage']);
    $server->addGrantType(new OAuth2_GrantType_AuthorizationCode($app['oauth_storage']));
    return $server;
};

$app['oauth_request'] = function ($app) {
    return OAuth2_Request::createFromGlobals();
};

/* set up routes / controllers */
// please see the Controller classes in src/Demo/Controller and src/LockdIn/Controller for more information
$app->mount('/api', new LockdIn\ApiControllerProvider());
$app->mount('/demo', new Demo\DemoControllerProvider());

$app->get('/', function() use($app) {
    return $app['twig']->render('demo/index.twig');
})->bind('homepage');

$app->run();

// turn the OAuth2_Response object into a Symfony HttpFoundation Response
function symfony_response(\OAuth2_Response $response)
{
    return new Symfony\Component\HttpFoundation\Response($response->getResponseBody(), $response->getStatusCode(), $response->getHttpHeaders());
}