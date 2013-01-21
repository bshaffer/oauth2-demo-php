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

        $controllers->get('/authorized', function(Application $app) {
            $server = $app['oauth_server'];

            // the user denied the authorization request
            if (!$code = $app['request']->get('code')) {
                return $app['twig']->render('demo/denied.twig');
            }

            // exchange authorization code for access token
            $query = array(
                'grant_type' => 'authorization_code',
                'code'       => $code,
                'client_id'  => 'demoapp',
                'client_secret' => 'demopass',
                'redirect_uri'  => $app['url_generator']->generate('authorize_redirect', array(), true),
            );

            // call the API using curl
            $curl = new Curl();
            $endpoint = $app['url_generator']->generate('grant', array(), true);
            $response = $curl->request($endpoint, $query, 'POST');
            if (!json_decode($response['response'], true)) {
                // something went wrong - show the raw response
                exit($response['response']);
            }
            $response['response'] = json_decode($response['response'], true);

            // render error if applicable
            $error = array();
            if ($response['errorNumber']) {
                // cURL error
                $error['error_description'] = $response['errorMessage'];
            } else {
                // OAuth error
                $error = $response['response'];
            }

            // if it is succesful, call the API with the retrieved token
            if ($response['response']['access_token']) {
                $token = $response['response']['access_token'];
                // make request to the API for awesome data
                $endpoint = $app['url_generator']->generate('access', array('access_token' => $token), true);
                $response = $curl->request($endpoint);
                return $app['twig']->render('demo/granted.twig', array('response' => json_decode($response['response'], true), 'token' => $token, 'endpoint' => $endpoint));
            }

            return $app['twig']->render('demo/error.twig', array('error' => $error));
        })->bind('authorize_redirect');

        return $controllers;
    }
}