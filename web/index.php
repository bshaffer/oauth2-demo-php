<?php

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../views',
));
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app['debug'] = true;

$storage = new OAuth2_Storage_Pdo(array('dsn' => 'sqlite://'.__DIR__.'/../data/oauth.sqlite'));
$server = new OAuth2_Server($storage);
$server->addGrantType(new OAuth2_GrantType_AuthorizationCode($storage));

$app->get('/', function() use($app) {
    return $app['twig']->render('demo/index.twig');
})->bind('homepage');

$app->get('/demo/authorized', function() use($app, $server) {
    // the user denied the authorization request
    if (!$code = $app['request']->get('code')) {
        return $app['twig']->render('demo/denied.twig');
    }
    // exchange authorization code for access token
    $query = array(
        'grant_type' => 'code',
        'code'       => $code,
        'client_id'  => 'demoapp',
        'client_secret' => 'demopass',
    );
    $endpoint = $app['url_generator']->generate('grant', $query, true);
    $curl = new Demo\Curl();
    $response = $curl->request($endpoint);
    $response['response'] = json_decode($response['response'], true);

    $error = '';
    if ($response['errorNumber'] || $response['response']['error']) {
        $error = $response['errorMessage'] ?: $response['response']['error_description'];
    }

    if ($response['response']['access_token']) {
        $token = $response['response']['access_token'];
        // make request to the API for awesome data
        $endpoint = $app['url_generator']->generate('access', array('access_token' => $token), true);
        $response = $curl->request($endpoint);
        return $app['twig']->render('demo/granted.twig', array('response' => json_decode($response['response'], true)));
    }

    return $app['twig']->render('demo/error.twig', array('error' => $error));
})->bind('authorize_redirect');

$app->get('/api/authorize', function() use($app, $server) {
    if (!$server->validateAuthorizeRequest(OAuth2_Request::createFromGlobals())) {
        return symfony_response($server->getResponse());
    }
    return $app['twig']->render('api/authorize.twig');
})->bind('authorize');

$app->post('/api/authorize', function() use($app, $server) {
    $authorized = (bool) $app['request']->request->get('authorize');
    return symfony_response($server->handleAuthorizeRequest(OAuth2_Request::createFromGlobals(), $authorized));
})->bind('authorize_post');

$app->get('/api/grant', function() use($app, $server) {
    return symfony_response($server->handleGrantRequest(OAuth2_Request::createFromGlobals()));
})->bind('grant');

$app->get('/api/access', function() use($app, $server) {
    if (!$server->verifyAccessRequest(OAuth2_Request::createFromGlobals())) {
        return symfony_response($server->getResponse());
    } else {
        return new Symfony\Component\HttpFoundation\Response(json_encode(array('friends' => array('john', 'matt', 'jane'))));
    }
})->bind('access');

$app->run();

// turn the OAuth2_Response object into a Symfony HttpFoundation Response
function symfony_response(OAuth2_Response $response)
{
    return new Symfony\Component\HttpFoundation\Response($response->getResponseBody(), $response->getStatusCode(), $response->getHttpHeaders());
}
