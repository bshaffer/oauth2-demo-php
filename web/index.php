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
    if (!file_exists($sqliteDir = __DIR__.'/../data/oauth.sqlite')) {
        // generate sqlite if it does not exist
        include_once(__DIR__.'/../data/rebuild_db.php');
    }
    return new OAuth2_Storage_Pdo(array('dsn' => 'sqlite:'.$sqliteDir));
};

$app['oauth_server'] = function($app) {
    /* OAuth2\HttpFondation\Server is a wrapper for OAuth2_Server which returns HttpFoundation\Request instead of OAuth2_Request */
    $server = new OAuth2\HttpFoundationBridge\Server($app['oauth_storage']);
    $server->addGrantType(new OAuth2_GrantType_AuthorizationCode($app['oauth_storage']));
    return $server;
};

// load the services
$containerFile = __DIR__.'/../data/services.json';
if (!file_exists($containerFile)) {
    // allows you to customize container file
    $containerFile = $containerFile.'.dist';
}

$app['parameters'] = json_decode(file_get_contents($containerFile), true);

/* set up routes / controllers */
// please see the Controller classes in src/Demo/Controller and src/LockdIn/Controller for more information
$app->mount('/lockdin', new LockdIn\ControllerProvider());
$app->mount('/demo', new Demo\ControllerProvider());

$app->get('/', function() use($app) {
    return $app['twig']->render('demo/index.twig');
})->bind('homepage');

// create an http foundation request implementing OAuth2_RequestInterface
$request = OAuth2\HttpFoundationBridge\Request::createFromGlobals();
$app->run($request);