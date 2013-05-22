<?php

namespace OAuth2Demo\Server\Controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Response;

class Resource
{
    // Connects the routes in Silex
    static public function addRoutes($routing)
    {
        $routing->get('/resource', array(new self(), 'resource'))->bind('access');
    }

    /**
     * This is called by the client app once the client has obtained an access
     * token for the current user.  If the token is valid, the resource (in this
     * case, the "friends" of the current user and his simulated user ID) will be
     * returned to the client
     */
    public function resource(Application $app)
    {
        // get the oauth server (configured in src/OAuth2Demo/Server/Server.php)
        $server = $app['oauth_server'];
        $request = $app['request'];

        if (!$server->verifyResourceRequest($request)) {
            return $server->getResponse();
        } else {
            // Get user ID
            $accessTokenData = $server->getAccessTokenData($request);
            $userId = $accessTokenData['user_id'];

            // return a fake API response - not that exciting
            // @TODO return something more valuable, like the name of the logged in user
            $api_response = array(
                'user_id' => $userId,
                'friends' => array(
                    'john',
                    'matt',
                    'jane'
                )
            );
            return new Response(json_encode($api_response));
        }
    }
}
