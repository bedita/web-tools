<?php
/**
 * BEdita, API-first content management framework
 * Copyright 2018 ChannelWeb Srl, Chialab Srl
 *
 * This file is part of BEdita: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * See LICENSE.LGPL or <http://gnu.org/licenses/lgpl-3.0.html> for more details.
 */
namespace BEdita\WebTools\Test\TestCase\Error;

use BEdita\WebTools\Error\ExceptionRenderer;
use BEdita\WebTools\View\TwigView;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\Network\Exception\InternalErrorException;
use Cake\Network\Exception\NotFoundException;
use Cake\TestSuite\TestCase;
use Cake\View\View;
use TestApp\Controller\TestController;
use TestApp\View\AppView;

/**
 * @coversDefaultClass \BEdita\WebTools\Error\ExceptionRenderer
 */
class ExceptionRendererTest extends TestCase
{
    /**
     * Data provider for `testTemplate` test case.
     *
     * @return array
     */
    public function templateProvider() : array
    {
        return [
            '400 exception' => [
                new NotFoundException('hello'),
                'error400',
            ],
            '500 exception' => [
                new InternalErrorException('hello'),
                'error500',
            ],
        ];
    }

    /**
     * Test error detail on response
     *
     * @param \Exception $exception Expected error.
     * @param string $expected Template.
     * @return void
     *
     * @dataProvider templateProvider
     * @covers ::_template()
     */
    public function testTemplate(\Exception $exception, $expected)
    {
        $renderer = new ExceptionRenderer($exception);
        $renderer->controller = new TestController();
        $response = $renderer->render();
        static::assertEquals($expected, $renderer->template);
    }

    /**
     * Test that failing to render an Exception error fallback first to app 500 error template.
     *
     * Since in TestApp misses the `Error/error404.twig` template,
     * a `Cake\View\Exception\MissingTemplateException` will be thrown trying to render the view
     * and the `Error/error500.twig` will be used.
     *
     * @return void
     *
     * @covers ::_outputMessageSafe()
     */
    public function testOutputMessageSafe()
    {
        $trigger = 0;
        $callback = function (Event $event) use ($trigger, &$callback) {
            // assure the callback is called just one time
            $trigger++;
            static::assertEquals(1, $trigger);

            static::assertInstanceOf(AppView::class, $event->getSubject());

            // remove the listener
            EventManager::instance()->off('View.beforeRender', $callback);
        };
        EventManager::instance()->on('View.beforeRender', $callback);

        $renderer = new ExceptionRenderer(new NotFoundException('hello'));
        $renderer->controller = new TestController();
        $customErrorMessage = 'Gustavo, take care of it.';
        $renderer->controller->set(compact('customErrorMessage'));
        $response = $renderer->render();

        $body = (string)$response->getBody();
        $expected = sprintf('AppView error 500: %s', $customErrorMessage);
        static::assertContains($expected, $body);
    }

    /**
     * Test the fallback if `TestApp\View\AppView` error render fails in safe mode.
     * In that case the `\Cake\View\View` class is used.
     *
     * @return void
     *
     * @covers ::_outputMessageSafe()
     */
    public function testOutputMessageSafeFallback()
    {
        $trigger = 0;
        $callback = function (Event $event) use (&$trigger, &$callback) {
            // assure the callback is called just two times
            $trigger++;
            static::assertGreaterThanOrEqual(1, $trigger);
            static::assertLessThanOrEqual(2, $trigger);

            // first time trying to use AppView then we throw a new exception
            if ($trigger === 1) {
                static::assertInstanceOf(AppView::class, $event->getSubject());
                // throw a new exception
                throw new \Exception('Oh my, another exception is here.');
            }

            // second time is the fallback then we can remove the listener
            if ($trigger === 2) {
                static::assertInstanceOf(View::class, $event->getSubject());
                // remove the listener
                EventManager::instance()->off('View.beforeRender', $callback);
            }
        };

        EventManager::instance()->on('View.beforeRender', $callback);

        $expected = 'The original error is here';
        $renderer = new ExceptionRenderer(new \Exception($expected));
        $renderer->controller = new TestController();
        $response = $renderer->render();

        $body = (string)$response->getBody();
        static::assertContains($expected, $body);
    }
}
