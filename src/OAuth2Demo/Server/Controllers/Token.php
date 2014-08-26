<?php

namespace OAuth2Demo\Server\Controllers;

use Silex\Application;

class Token
{
    // Connects the routes in Silex
    public static function addRoutes($routing)
    {
        $routing->post('/token', array(new self(), 'token'))->bind('grant');
    }

    /**
     * This is called by the client app once the client has obtained
     * an authorization code from the Authorize Controller (@see OAuth2Demo\Server\Controllers\Authorize).
     * If the request is valid, an access token will be returned
     */
    public function token(Application $app)
    {
        // get the oauth server (configured in src/OAuth2Demo/Server/Server.php)
        $server = $app['oauth_server'];

        // get the oauth response (configured in src/OAuth2Demo/Server/Server.php)
        $response = $app['oauth_response'];

        // let the oauth2-server-php library do all the work!
        return $server->handleTokenRequest($app['request'], $response);
    }
}
