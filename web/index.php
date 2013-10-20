<?php

require_once __DIR__.'/../vendor/autoload.php';

/** show all errors! */
ini_set('display_errors', 1);
error_reporting(E_ALL);

/** set up the silex application object */
$app = new Silex\Application();
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../views',
));
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\SessionServiceProvider());

$app['debug'] = true;

/** start the session */
if (!$app['session']->isStarted()) {
    $app['session']->start();
}

/** load the parameters configuration */
$parameterFile = __DIR__.'/../data/parameters.json';
if (!file_exists($parameterFile)) {
    // allows you to customize parameter file
    $parameterFile = $parameterFile.'.dist';
}
$app['environments'] = array();
if (!$parameters = json_decode(file_get_contents($parameterFile), true)) {
    throw new Exception('unable to parse parameters file: '.$parameterFile);
}
// we are using an array of configurations
if (!isset($parameters['client_id'])) {
    $app['environments'] = array_keys($parameters);
    $env = $app['session']->get('config_environment');
    $parameters = isset($parameters[$env]) ? $parameters[$env] : array_shift($parameters);
}
$app['parameters'] = $parameters;

/** set up routes / controllers */
$app->mount('/', new OAuth2Demo\Client\Client());
$app->mount('/lockdin', new OAuth2Demo\Server\Server());

// create an http foundation request implementing OAuth2\RequestInterface
$request = OAuth2\HttpFoundationBridge\Request::createFromGlobals();
$app->run($request);
