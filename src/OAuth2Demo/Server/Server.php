<?php

namespace OAuth2Demo\Server;

use Silex\Application;
use Silex\ControllerProviderInterface;
use OAuth2\HttpFoundationBridge\Response as BridgeResponse;
use OAuth2\Server as OAuth2Server;
use OAuth2\Storage\Pdo;
use OAuth2\GrantType\AuthorizationCode;
use OAuth2\GrantType\UserCredentials;

class Server implements ControllerProviderInterface
{
    /**
     * function to create the OAuth2 Server Object
     */
    public function setup(Application $app)
    {
        // ensure our Sqlite database exists
        if (!file_exists($sqliteFile = __DIR__.'/../../../data/oauth.sqlite')) {
            $this->generateSqliteDb();
        }

        // create PDO-based sqlite storage
        $storage = new Pdo(array('dsn' => 'sqlite:'.$sqliteFile));

        // create array of supported grant types
        $grantTypes = array(
            'authorization_code' => new AuthorizationCode($storage),
            'user_credentials'   => new UserCredentials($storage),
        );

        // instantiate the oauth server
        $server = new OAuth2Server($storage, array('enforce_state' => true, 'allow_implicit' => true), $grantTypes);

        // add the server to the silex "container" so we can use it in our controllers (see src/OAuth2Demo/Server/Controllers/.*)
        $app['oauth_server'] = $server;

        /**
         * add HttpFoundataionBridge Response to the container, which returns a silex-compatible response object
         * @see (https://github.com/bshaffer/oauth2-server-httpfoundation-bridge)
         */
        $app['oauth_response'] = new BridgeResponse();
    }

    /**
     * Connect the controller classes to the routes
     */
    public function connect(Application $app)
    {
        // create the oauth2 server object
        $this->setup($app);

        // creates a new controller based on the default route
        $routing = $app['controllers_factory'];

        /* Set corresponding endpoints on the controller classes */
        Controllers\Authorize::addRoutes($routing);
        Controllers\Token::addRoutes($routing);
        Controllers\Resource::addRoutes($routing);

        return $routing;
    }

    private function generateSqliteDb()
    {
        include_once(__DIR__.'/../../../data/rebuild_db.php');
    }
}