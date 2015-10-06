<?php

namespace OAuth2Demo\Client\Controllers;

use Silex\Application;

class ReceiveImplicitToken
{
    public static function addRoutes($routing)
    {
        $routing->get('/client/receive_implicit_token', array(new self(), 'receiveImplicitToken'))->bind('authorize_redirect_implicit');
    }

    public function receiveImplicitToken(Application $app)
    {
        $request = $app['request']; // the request object
        $twig    = $app['twig'];    // used to render twig templates

        // the user denied the authorization request
        if ($request->get('error')) {
            return $twig->render('client/failed_token_request.twig', array('response' => $request->getAllQueryParameters()));
        }

        // nothing to do - implicit tokens are in the URL Fragment, so it must be done by the browser

        return $twig->render('client/show_implicit_token.twig');
    }
}
