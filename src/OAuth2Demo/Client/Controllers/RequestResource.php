<?php

namespace OAuth2Demo\Client\Controllers;

use Silex\Application;

class RequestResource
{
    public static function addRoutes($routing)
    {
        $routing->get('/client/request_resource', array(new self(), 'requestResource'))->bind('request_resource');
    }

    public function requestResource(Application $app)
    {
        $twig   = $app['twig'];          // used to render twig templates
        $config = $app['parameters'];    // the configuration for the current oauth implementation
        $urlgen = $app['url_generator']; // generates URLs based on our routing
        $http   = $app['http_client'];   // service to make HTTP requests to the oauth server

        // pull the token from the request
        $token = $app['request']->get('token');

        // make the resource request with the token in the request body
        $config['resource_params']['access_token'] = $token;

        // determine the resource endpoint to call based on our config (do this somewhere else?)
        $apiRoute = $config['resource_route'];
        $endpoint = 0 === strpos($apiRoute, 'http') ? $apiRoute : $urlgen->generate($apiRoute, $config['resource_params'], true);

        // make the resource request and decode the json response
        $request = $http->get($endpoint, null, $config['http_options']);
        $request->getQuery()->set('access_token', $token);
        $response = $request->send();
        $json = json_decode((string) $response->getBody(), true);

        $resource_uri = sprintf('%s%saccess_token=%s', $endpoint, false === strpos($endpoint, '?') ? '?' : '&', $token);

        return $twig->render('client/show_resource.twig', array('response' => $json ? $json : $response, 'resource_uri' => $resource_uri));
    }
}
