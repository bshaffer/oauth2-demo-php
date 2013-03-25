<?php

namespace Demo;

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

        $controllers->post('/authorized', function(Application $app) {
            $app['session']->set('config_environment', $app['request']->get('environment'));
            return $app->redirect($app['url_generator']->generate('homepage'));
        })->bind('set_environment');

        $controllers->get('/authorized', function(Application $app) {
            $server = $app['oauth_server'];

            // the user denied the authorization request
            if (!$code = $app['request']->get('code')) {
                return $app['twig']->render('demo/denied.twig');
            }

            // exchange authorization code for access token
            $query = array(
                'grant_type'    => 'authorization_code',
                'code'          => $code,
                'client_id'     => $app['parameters']['client_id'],
                'client_secret' => $app['parameters']['client_secret'],
                'redirect_uri'  => $app['url_generator']->generate('authorize_redirect', array(), true),
            );

            // call the API using curl
            $curl = new Curl();
            $grantRoute = $app['parameters']['token_route'];
            $endpoint = 0 === strpos($grantRoute, 'http') ? $grantRoute : $app['url_generator']->generate($grantRoute, array(), true);

            $response = $curl->request($endpoint, $query, 'POST', $app['parameters']['curl_options']);
            $json = json_decode($response['response'], true);

            // render error if applicable
            $error = array();
            if ($response['errorNumber']) {
                // cURL error
                $error['error_description'] = $response['errorMessage'];
            } else {
                // OAuth error
                $error = $json;
            }

            // if it is succesful, call the API with the retrieved token
            if (isset($json['access_token'])) {
                $token = $json['access_token'];
                // make request to the API for awesome data
                $params = array_merge(array('access_token' => $token), $app['parameters']['resource_params']);
                $apiRoute = $app['parameters']['resource_route'];
                $endpoint = 0 === strpos($apiRoute, 'http') ? $apiRoute : $app['url_generator']->generate($apiRoute, array(), true);
                $response = $curl->request($endpoint, $params, $app['parameters']['resource_method'], $app['parameters']['curl_options']);
                $json = json_decode($response['response'], true);
                return $app['twig']->render('demo/granted.twig', array('response' => $json ? $json : $response, 'token' => $token, 'endpoint' => $endpoint));
            }

            return $app['twig']->render('demo/error.twig', array('response' => $error));
        })->bind('authorize_redirect');

        return $controllers;
    }
}
