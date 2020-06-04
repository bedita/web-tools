<?php

namespace TestApp;

use BEdita\WebTools\BaseApplication;
use Cake\Routing\RouteBuilder;

/**
 * Application setup class.
 *
 * This defines the bootstrapping logic and middleware layers you
 * want to use in your application.
 */
class Application extends BaseApplication
{
    /**
     * {@inheritDoc}
     */
    public function bootstrap()
    {
        // Call parent to load bootstrap from files.
        parent::bootstrap();

        // Load WebTools plugin
        $this->addPlugin('BEdita/WebTools', ['bootstrap' => true, 'path' => dirname(dirname(dirname(__DIR__))) . DS]);
    }

    /**
     * {@inheritDoc}
     */
    public function routes($routes)
    {
        // add rules for ApiProxyTrait
        $routes->scope('/api', ['_namePrefix' => 'api:'], function (RouteBuilder $routes) {
            $routes->get('/**', ['controller' => 'Api', 'action' => 'get'], 'get');
        });
    }
}
