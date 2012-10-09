<?php

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();

$storage = new OAuth2_Storage_Pdo();
$server = new OAuth2_Server();

$app->get('/authorize', function($name) use($app, $server) {
    return 'Hello '.$app->escape($name);
});

$app->run();
