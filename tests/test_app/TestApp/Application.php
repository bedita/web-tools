<?php
declare(strict_types=1);

namespace TestApp;

use BEdita\WebTools\Plugin;
use Cake\Http\BaseApplication;
use Cake\Http\MiddlewareQueue;
use Cake\Routing\Middleware\RoutingMiddleware;
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
     *
     * Do not require config/bootstrap.php
     */
    public function bootstrap(): void
    {
        $this->addPlugin(new Plugin());
    }

    /**
     * @inheritDoc
     */
    public function routes(RouteBuilder $routes): void
    {
        // add rules for ApiProxyTrait
        $routes->scope('/api', ['_namePrefix' => 'api:'], function (RouteBuilder $routes) {
            $routes->get('/**', ['controller' => 'Api', 'action' => 'get'], 'get');
            $routes->post('/**', ['controller' => 'Api', 'action' => 'post'], 'post');
            $routes->patch('/**', ['controller' => 'Api', 'action' => 'patch'], 'patch');
            $routes->delete('/**', ['controller' => 'Api', 'action' => 'delete'], 'delete');
        });
    }

    /**
     * @inheritDoc
     */
    public function middleware(MiddlewareQueue $middleware): MiddlewareQueue
    {
        $middleware->add(new RoutingMiddleware($this));

        return $middleware;
    }
}
