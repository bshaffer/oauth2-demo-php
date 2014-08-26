<?php

namespace OAuth2Demo\Client\Controllers;

use Silex\Application;

class ReceiveAuthorizationCode
{
    public static function addRoutes($routing)
    {
        $routing->get('/client/receive_authcode', array(new self(), 'receiveAuthorizationCode'))->bind('authorize_redirect');
    }

    public function receiveAuthorizationCode(Application $app)
    {
        $request = $app['request']; // the request object
        $session = $app['session']; // the session (or user) object
        $twig    = $app['twig'];    // used to render twig templates

        // the user denied the authorization request
        if (!$code = $request->get('code')) {
            return $twig->render('client/failed_authorization.twig', array('response' => $request->getAllQueryParameters()));
        }

        // verify the "state" parameter matches this user's session (this is like CSRF - very important!!)
        if ($request->get('state') !== $session->getId()) {
            return $twig->render('client/failed_authorization.twig', array('response' => array('error_description' => 'Your session has expired.  Please try again.')));
        }

        return $twig->render('client/show_authorization_code.twig', array('code' => $code));
    }
}
