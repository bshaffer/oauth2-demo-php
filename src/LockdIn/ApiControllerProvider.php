<?php

namespace LockdIn;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Response;

class ApiControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];

        $controllers->get('/authorize', function (Application $app) {
            if (!$app['oauth_server']->validateAuthorizeRequest($app['oauth_request'])) {
                return symfony_response($app['oauth_server']->getResponse());
            }
            return $app['twig']->render('api/authorize.twig');
        })->bind('authorize');

        $controllers->post('/authorize', function (Application $app) {
            $authorized = (bool) $app['request']->request->get('authorize');
            return symfony_response($app['oauth_server']->handleAuthorizeRequest($app['oauth_request'], $authorized));
        })->bind('authorize_post');

        $controllers->get('/grant', function(Application $app) {
            return symfony_response($app['oauth_server']->handleGrantRequest($app['oauth_request']));
        })->bind('grant');

        $controllers->get('/access', function(Application $app) {
            $server = $app['oauth_server'];
            if (!$server->verifyAccessRequest($app['oauth_request'])) {
                return symfony_response($server->getResponse());
            } else {
                return new Response(json_encode(array('friends' => array('john', 'matt', 'jane'))));
            }
        })->bind('access');

        return $controllers;
    }
}