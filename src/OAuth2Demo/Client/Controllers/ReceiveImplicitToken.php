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
        $twig    = $app['twig'];    // used to render twig templates

        // nothing to do - implicit tokens are in the URL Fragment, so it must be done by the browser

        return $twig->render('client/show_implicit_token.twig');
    }
}
