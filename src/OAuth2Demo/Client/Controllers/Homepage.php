<?php

namespace OAuth2Demo\Client\Controllers;

use Silex\Application;

class Homepage
{
    // Connects the routes in Silex
    public static function addRoutes($routing)
    {
        $routing->get('/', array(new self(), 'homepage'))->bind('homepage');
        $routing->post('/set-environment', array(new self(), 'setEnvironment'))->bind('set_environment');
    }

    public function homepage(Application $app)
    {
        // render the homepage
        if (!$app['session']->isStarted()) {
            $app['session']->start();
        }

        return $app['twig']->render('client/index.twig', array('session_id' => $app['session']->getId()));
    }

    public function setEnvironment(Application $app)
    {
        // used when an environmental configuration is set (see "Test Your Own OAuth2 Server" in the README)
        $app['session']->set('config_environment', $app['request']->get('environment'));

        return $app->redirect($app['url_generator']->generate('homepage'));
    }
}
