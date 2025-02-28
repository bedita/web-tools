<?php
declare(strict_types=1);

/**
 * BEdita, API-first content management framework
 * Copyright 2022 ChannelWeb Srl, Chialab Srl
 *
 * This file is part of BEdita: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * See LICENSE.LGPL or <http://gnu.org/licenses/lgpl-3.0.html> for more details.
 */
namespace BEdita\WebTools\Test\TestCase\Middleware;

use Authentication\Authenticator\Result;
use Authentication\Authenticator\ResultInterface;
use BEdita\WebTools\Authenticator\OAuth2Authenticator;
use BEdita\WebTools\Middleware\OAuth2Middleware;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * {@see BEdita\WebTools\Middleware\OAuth2Middleware} Test Case
 */
#[CoversClass(OAuth2Middleware::class)]
class OAuth2MiddlewareTest extends TestCase
{
    /**
     * Request Handler class
     *
     * @var \Psr\Http\Server\RequestHandlerInterface
     */
    protected $handler;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->handler = new class () implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new Response(['status' => 204]);
            }
        };
    }

    /**
     * Test `process` with no authentication result.
     *
     * @return void
     */
    #[CoversMethod(OAuth2Middleware::class, 'process')]
    public function testNoResult(): void
    {
        $request = new ServerRequest();
        $middleware = new OAuth2Middleware();
        $response = $middleware->process($request, $this->handler);

        static::assertInstanceOf(Response::class, $response);
        static::assertEquals(204, $response->getStatusCode());
    }

    /**
     * Test `process` with authentication result but without `authUrl`.
     *
     * @return void
     */
    #[CoversMethod(OAuth2Middleware::class, 'process')]
    public function testResultNoAuth(): void
    {
        $request = new ServerRequest();
        $result = new Result(['key' => 'value'], ResultInterface::SUCCESS);
        $request = $request->withAttribute('authenticationResult', $result);

        $middleware = new OAuth2Middleware();
        $response = $middleware->process($request, $this->handler);

        static::assertInstanceOf(Response::class, $response);
        static::assertEquals(204, $response->getStatusCode());
    }

    /**
     * Test `process` with authentication with `authUrl`.
     *
     * @return void
     */
    #[CoversMethod(OAuth2Middleware::class, 'process')]
    public function testResultAuth(): void
    {
        $request = new ServerRequest();
        $data = [OAuth2Authenticator::AUTH_URL_KEY => 'http://example.com'];
        $result = new Result($data, ResultInterface::SUCCESS);
        $request = $request->withAttribute('authenticationResult', $result);

        $middleware = new OAuth2Middleware();
        $response = $middleware->process($request, $this->handler);

        static::assertEquals(302, $response->getStatusCode());
        static::assertEquals('http://example.com', $response->getHeaderLine('Location'));
    }
}
