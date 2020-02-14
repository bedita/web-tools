<?php

namespace TestApp;

use BEdita\WebTools\BaseApplication;

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
    public function bootstrap(): void
    {
        // Call parent to load bootstrap from files.
        parent::bootstrap();

        // Load WebTools plugin
        $this->addPlugin('BEdita/WebTools', ['bootstrap' => true, 'path' => dirname(dirname(__DIR__)) . DS]);
    }
}
