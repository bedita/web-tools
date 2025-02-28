<?php
declare(strict_types=1);

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

use BEdita\SDK\BEditaClientException;
use BEdita\WebTools\Error\ExceptionRenderer;
use Cake\Controller\ErrorController;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\Http\Exception\InternalErrorException;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;
use Cake\View\View;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use TestApp\Controller\TestController;
use TestApp\View\AppView;
use Throwable;

/**
 * @see \BEdita\WebTools\Error\ExceptionRenderer
 */
#[CoversClass(ExceptionRenderer::class)]
class ExceptionRendererTest extends TestCase
{
    /**
     * Get Extension class with utility methods use in tests
     *
     * @param \Throwable $exception Exception.
     * @param \Cake\Http\ServerRequest|null $request The request if this is set it will be used
     * @return ExceptionRenderer
     */
    protected function extensionClass(Throwable $error, ?ServerRequest $request = null)
    {
        return new class ($error, $request) extends ExceptionRenderer
        {
            public function getTemplate()
            {
                return $this->template;
            }

            public function setController($controller)
            {
                return $this->controller = $controller;
            }

            public function setError(Throwable $throwable)
            {
                return $this->error = $throwable;
            }
        };
    }

    /**
     * Data provider for `testTemplate` test case.
     *
     * @return array
     */
    public static function templateProvider(): array
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
            '503 BEditaClientException' => [
                new BEditaClientException('hello'),
                'error500',
            ],
            '404 BEditaClientException' => [
                new BEditaClientException('hello', 404),
                'error400',
            ],
        ];
    }

    /**
     * Test error detail on response
     *
     * @param \Exception $exception Expected error.
     * @param string $expected Template.
     * @return void
     * @covers ::_template()
     * @covers ::getHttpCode()
     */
    #[DataProvider('templateProvider')]
    public function testTemplate(Exception $exception, $expected)
    {
        $renderer = $this->extensionClass($exception);
        $renderer->setController(new ErrorController(new ServerRequest([])));
        $renderer->render();
        static::assertEquals($expected, $renderer->getTemplate());
    }

    /**
     * Test that failing to render an Exception error fallback first to app 500 error template.
     *
     * Since in TestApp misses the `Error/error404.twig` template,
     * a `Cake\View\Exception\MissingTemplateException` will be thrown trying to render the view
     * and the `Error/error500.twig` will be used.
     *
     * @return void
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

        $renderer = $this->extensionClass(new NotFoundException('hello'));
        $controller = new ErrorController(new ServerRequest([]));
        $customErrorMessage = 'Gustavo, take care of it.';
        $controller->set(compact('customErrorMessage'));
        $renderer->setController($controller);

        $response = $renderer->render();

        $body = (string)$response->getBody();
        $expected = sprintf('AppView error 500: %s', $customErrorMessage);
        static::assertStringContainsString($expected, $body);
    }

    /**
     * Test the fallback if `TestApp\View\AppView` error render fails in safe mode.
     * In that case the `\Cake\View\View` class is used.
     *
     * @return void
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
                throw new Exception('Oh my, another exception is here.');
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
        $renderer = $this->extensionClass(new Exception($expected));
        $renderer->setController(new TestController(new ServerRequest([])));
        $response = $renderer->render();

        $body = (string)$response->getBody();
        static::assertStringContainsString($expected, $body);
    }

    /**
     * Test `_getController` method with `application/json` Accept header
     *
     * @return void
     * @covers ::_getController()
     */
    public function testControllerJsonResponse(): void
    {
        $request = (new ServerRequest())->withHeader('Accept', 'application/json');
        $renderer = $this->extensionClass(new NotFoundException(), $request);
        $response = $renderer->render();

        $body = (string)$response->getBody();
        $error = json_decode($body, true);
        $jsonErr = json_last_error();
        static::assertEquals(JSON_ERROR_NONE, $jsonErr);
        unset($error['file'], $error['line']);
        $expected = [
            'message' => 'Not Found',
            'url' => '/',
            'code' => 404,
        ];
        static::assertEquals($expected, $error);
    }
}
