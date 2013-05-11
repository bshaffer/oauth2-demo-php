<?php

namespace OAuth2Demo\Server\Controllers;

use Silex\Application;

class Authorize
{
    // Connects the routes in Silex
    static public function addRoutes($routing)
    {
        $routing->get('/authorize', array(new self(), 'authorize'))->bind('authorize');
        $routing->post('/authorize', array(new self(), 'authorizeFormSubmit'))->bind('authorize_post');
    }

    public function authorize(Application $app)
    {
        // get the oauth server (configured in src/OAuth2Demo/Server/Server.php)
        $server = $app['oauth_server'];

        if (!$server->validateAuthorizeRequest($app['request'])) {
            return $server->getResponse();
        }

        return $app['twig']->render('server/authorize.twig');
    }

    public function authorizeFormSubmit(Application $app)
    {
        // get the oauth server (configured in src/OAuth2Demo/Server/Server.php)
        $server = $app['oauth_server'];

        // check the form data to see if the user authorized the request
        $authorized = (bool) $app['request']->request->get('authorize');

        // call the oauth server and return the response
        return $server->handleAuthorizeRequest($app['request'], $authorized);
    }
}