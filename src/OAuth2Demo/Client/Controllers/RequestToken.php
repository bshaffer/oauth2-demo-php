<?php

namespace OAuth2Demo\Client\Controllers;

use Silex\Application;
use Guzzle\Http\Client;

class RequestToken
{
    static public function addRoutes($routing)
    {
        $routing->get('/client/request_token/authorization_code', array(new self(), 'requestTokenWithAuthCode'))->bind('request_token_with_authcode');
        $routing->get('/client/request_token/user_credentials', array(new self(), 'requestTokenWithUserCredentials'))->bind('request_token_with_usercredentials');

    }

    public function requestTokenWithAuthCode(Application $app)
    {
        $twig   = $app['twig'];          // used to render twig templates
        $config = $app['parameters'];    // the configuration for the current oauth implementation
        $urlgen = $app['url_generator']; // generates URLs based on our routing

        $code = $app['request']->get('code');

        // exchange authorization code for access token
        $query = array(
            'grant_type'    => 'authorization_code',
            'code'          => $code,
            'client_id'     => $config['client_id'],
            'client_secret' => $config['client_secret'],
            'redirect_uri'  => $urlgen->generate('authorize_redirect', array(), true),
        );

        // determine the token endpoint to call based on our config (do this somewhere else?)
        $grantRoute = $config['token_route'];
        $endpoint = 0 === strpos($grantRoute, 'http') ? $grantRoute : $urlgen->generate($grantRoute, array(), true);

        $options = array('debug' => true, 'exceptions' => false, 'proxy' => '127.0.0.1:8888');
        $http = new Client(null);
        $request = $http->post($endpoint, null, $query, $options);
        $response = $request->send();

        // make the token request via curl and decode the json response
        $response = $curl->request($endpoint, $query, 'POST', $config['curl_options']);
        $json = json_decode($response['response'], true);

        // if it is succesful, display the token in our app
        if (isset($json['access_token'])) {
            return $twig->render('client/show_access_token.twig', array('token' => $json['access_token']));
        }

        return $twig->render('client/failed_token_request.twig', array('response' => $json ? $json : $response));
    }

    public function requestTokenWithUserCredentials(Application $app)
    {
        $twig   = $app['twig'];          // used to render twig templates
        $config = $app['parameters'];    // the configuration for the current oauth implementation
        $urlgen = $app['url_generator']; // generates URLs based on our routing
        $curl   = $app['curl'];          // simple class used to make curl requests

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

        // determine the token endpoint to call based on our config (do this somewhere else?)
        $grantRoute = $config['token_route'];
        $endpoint = 0 === strpos($grantRoute, 'http') ? $grantRoute : $urlgen->generate($grantRoute, array(), true);

        // make the token request via curl and decode the json response
        $response = $curl->request($endpoint, $query, 'POST', $config['curl_options']);
        $json = json_decode($response['response'], true);

        // if it is succesful, display the token in our app
        if (isset($json['access_token'])) {
            return $twig->render('client/show_access_token.twig', array('token' => $json['access_token']));
        }

        return $twig->render('client/failed_token_request.twig', array('response' => $json ? $json : $response));
    }
}