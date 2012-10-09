<?php

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();
$app['debug'] = true;

$storage = new OAuth2_Storage_Pdo(array('dsn' => 'sqlite://'.__DIR__.'/../data/oauth.sqlite'));
$server = new OAuth2_Server($storage);

$app->get('/authorize', function() use($app, $server) {
    $server->handleAuthorizeRequest(OAuth2_Request::createFromGlobals())->send();
});

$app->get('/grant', function() use($app, $server) {
    $server->handleGrantRequest(OAuth2_Request::createFromGlobals())->send();
});

$app->get('/access', function() use($app, $server) {
    if (!$server->verifyAccessRequest(OAuth2_Request::createFromGlobals())) {
        $server->getResponse()->send();
    } else {
        die ("Congratulations.  You have successfully performed an OAuth request!");
    }
});

$app->run();
