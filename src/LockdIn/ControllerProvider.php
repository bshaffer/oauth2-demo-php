<?php

namespace LockdIn;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Response;

class ControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];

        /* AUTHORIZE endpoint */
        $controllers->get('/authorize', function (Application $app) {
            $server = $app['oauth_server'];
            if (!$server->validateAuthorizeRequest($app['request'])) {
                return $server->getResponse();
            }
            return $app['twig']->render('lockdin/authorize.twig');
        })->bind('authorize');

        $controllers->post('/authorize', function (Application $app) {
            $authorized = (bool) $app['request']->request->get('authorize');
            return $app['oauth_server']->handleAuthorizeRequest($app['request'], $authorized);
        })->bind('authorize_post');

        /* TOKEN endpoint */
        $controllers->post('/token', function(Application $app) {
            return $app['oauth_server']->handleTokenRequest($app['request']);
        })->bind('grant');

        /* RESOURCE endpoint */
        $controllers->get('/resource', function(Application $app) {
            $server = $app['oauth_server'];
            if (!$server->verifyResourceRequest($app['request'])) {
                return $server->getResponse();
            } else {
                return new Response(json_encode(array('friends' => array('john', 'matt', 'jane'))));
            }
        })->bind('access');

        return $controllers;
    }
}