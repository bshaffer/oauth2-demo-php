<?php

namespace OAuth2Demo\Client;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Response;

class Client implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        // creates a new controller based on the default route
        $routing = $app['controllers_factory'];

        /* Set the container */
        // ensures this runs on default port
        $port = is_numeric($_SERVER['SERVER_PORT']) ? intval($_SERVER['SERVER_PORT']) : 80;
        $app['curl'] = new Curl\Curl(array('http_port' => $port));
        // add twig extension
        $app['twig']->addExtension(new Twig\JsonStringifyExtension());

        /* Set corresponding endpoints on the controller classes */
        Controllers\Homepage::addRoutes($routing);
        Controllers\ReceiveAuthorizationCode::addRoutes($routing);
        Controllers\RequestToken::addRoutes($routing);
        Controllers\RequestResource::addRoutes($routing);
        Controllers\ReceiveImplicitToken::addRoutes($routing);

        return $routing;
    }
}
