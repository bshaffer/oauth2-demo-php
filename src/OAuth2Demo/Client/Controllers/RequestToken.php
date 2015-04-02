<?php

namespace OAuth2Demo\Client\Controllers;

use Silex\Application;

class RequestToken
{
    public static function addRoutes($routing)
    {
        $routing->get('/client/request_token/authorization_code', array(new self(), 'requestTokenWithAuthCode'))->bind('request_token_with_authcode');
        $routing->get('/client/request_token/user_credentials', array(new self(), 'requestTokenWithUserCredentials'))->bind('request_token_with_usercredentials');
        $routing->get('/client/request_token/refresh_token', array(new self(), 'requestTokenWithRefreshToken'))->bind('request_token_with_refresh_token');

    }

    public function requestTokenWithAuthCode(Application $app)
    {
        $twig   = $app['twig'];          // used to render twig templates
        $config = $app['parameters'];    // the configuration for the current oauth implementation
        $urlgen = $app['url_generator']; // generates URLs based on our routing
        $http   = $app['http_client'];   // service to make HTTP requests to the oauth server

        $code = $app['request']->get('code');

        $redirect_uri_params = array_filter(array(
            'show_refresh_token' => $app['request']->get('show_refresh_token'),
        ));

        // exchange authorization code for access token
        $query = array(
            'grant_type'    => 'authorization_code',
            'code'          => $code,
            'client_id'     => $config['client_id'],
            'client_secret' => $config['client_secret'],
            'redirect_uri'  => $urlgen->generate('authorize_redirect', $redirect_uri_params, true),
        );

        // determine the token endpoint to call based on our config
        $endpoint = $config['token_route'];
        if (0 !== strpos($endpoint, 'http')) {
            // if PHP's built in web server is being used, we cannot continue
            $this->testForBuiltInWebServer();

            // generate the route
            $endpoint = $urlgen->generate($endpoint, array(), true);
        }

        // make the token request via http and decode the json response
        $response = $http->post($endpoint, null, $query, $config['http_options'])->send();
        $json = json_decode((string) $response->getBody(), true);

        // if it is succesful, display the token in our app
        if (isset($json['access_token'])) {
            if ($app['request']->get('show_refresh_token')) {
                return $twig->render('client/show_refresh_token.twig', array('response' => $json));
            }

            return $twig->render('client/show_access_token.twig', array('response' => $json));
        }

        return $twig->render('client/failed_token_request.twig', array('response' => $json ? $json : $response));
    }

    public function requestTokenWithUserCredentials(Application $app)
    {
        $twig   = $app['twig'];          // used to render twig templates
        $config = $app['parameters'];    // the configuration for the current oauth implementation
        $urlgen = $app['url_generator']; // generates URLs based on our routing
        $http   = $app['http_client'];   // simple class used to make http requests

        $username = $app['request']->get('username');
        $password = $app['request']->get('password');

        // exchange user credentials for access token
        $query = array(
            'grant_type'    => 'password',
            'client_id'     => $config['client_id'],
            'client_secret' => $config['client_secret'],
            'username'      => $username,
            'password'      => $password,
        );

        // determine the token endpoint to call based on our config
        $endpoint = $config['token_route'];
        if (0 !== strpos($endpoint, 'http')) {
            // if PHP's built in web server is being used, we cannot continue
            $this->testForBuiltInWebServer();

            // generate the route
            $endpoint = $urlgen->generate($endpoint, array(), true);
        }

        // make the token request via http and decode the json response
        $response = $http->post($endpoint, null, $query, $config['http_options'])->send();
        $json = json_decode((string) $response->getBody(), true);

        // if it is succesful, display the token in our app
        if (isset($json['access_token'])) {
            return $twig->render('client/show_access_token.twig', array('response' => $json));
        }

        return $twig->render('client/failed_token_request.twig', array('response' => $json ? $json : $response));
    }

    public function requestTokenWithRefreshToken(Application $app)
    {
        $twig   = $app['twig'];          // used to render twig templates
        $config = $app['parameters'];    // the configuration for the current oauth implementation
        $urlgen = $app['url_generator']; // generates URLs based on our routing
        $http   = $app['http_client'];   // simple class used to make http requests

        $refreshToken = $app['request']->get('refresh_token');

        // exchange user credentials for access token
        $query = array(
            'grant_type'    => 'refresh_token',
            'client_id'     => $config['client_id'],
            'client_secret' => $config['client_secret'],
            'refresh_token' => $refreshToken,
        );

        // determine the token endpoint to call based on our config (do this somewhere else?)
        $grantRoute = $config['token_route'];
        $endpoint = 0 === strpos($grantRoute, 'http') ? $grantRoute : $urlgen->generate($grantRoute, array(), true);

        // make the token request via http and decode the json response
        $response = $http->post($endpoint, null, $query, $config['http_options'])->send();
        $json = json_decode((string) $response->getBody(), true);

        // if it is succesful, display the token in our app
        if (isset($json['access_token'])) {
            return $twig->render('client/show_access_token.twig', array('response' => $json));
        }

        return $twig->render('client/failed_token_request.twig', array('response' => $json ? $json : $response));
    }

    public function testForBuiltInWebServer()
    {
        if (php_sapi_name() == 'cli-server') {
            $message = 'As PHP\'s built-in web-server does not allow for concurrent requests, this will result in deadlock.';
            $message .= "<br /><br />";
            $message .= 'You must configure a virtualhost via Apache or another web server to continue.  Sorry!';
            exit($message);
        }
    }
}
