<?php
declare(strict_types=1);

/**
 * BEdita, API-first content management framework
 * Copyright 2025 ChannelWeb Srl, Chialab Srl
 *
 * This file is part of BEdita: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * See LICENSE.LGPL or <http://gnu.org/licenses/lgpl-3.0.html> for more details.
 */

namespace BEdita\WebTools\Test\TestCase\Http;

use BEdita\WebTools\Http\BaseClient;
use Cake\Core\Configure;
use Cake\Http\Client;
use Cake\Http\Client\Response;
use Cake\TestSuite\TestCase;
use Cake\Validation\Validator;
use InvalidArgumentException;
use Laminas\Diactoros\Stream;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * {@see \BEdita\WebTools\Http\BaseClient} Test Case
 */
#[CoversClass(BaseClient::class)]
class BaseClientTest extends TestCase
{
    /**
     * Test constructor against invalid configuration.
     *
     * @return void
     */
    #[CoversMethod(BaseClient::class, '__construct')]
    #[CoversMethod(BaseClient::class, 'validateConf')]
    #[CoversMethod(BaseClient::class, 'getValidator')]
    #[CoversMethod(BaseClient::class, 'createClient')]
    public function testInvalidConfig(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('client config not valid: {"url":{"_empty":"This field cannot be left empty"}}');
        $config = [
            'auth' => [
                'type' => 'BearerAccessToken',
            ],
            'logLevel' => 'error',
        ];
        new class ($config) extends BaseClient {
            public function getValidator(): Validator
            {
                return parent::getValidator();
            }
        };
    }

    /**
     * Basic test.
     *
     * @return void
     */
    #[CoversMethod(BaseClient::class, '__construct')]
    #[CoversMethod(BaseClient::class, 'validateConf')]
    #[CoversMethod(BaseClient::class, 'getValidator')]
    #[CoversMethod(BaseClient::class, 'createClient')]
    #[CoversMethod(BaseClient::class, 'defaultConfigName')]
    #[CoversMethod(BaseClient::class, 'getHttpClient')]
    public function testBase(): void
    {
        // note: key 'BaseClientTest.php:' with ':' because the class is anonymous
        Configure::write('BaseClientTest.php:', ['url' => 'https://example.com']);
        $config = [
            'auth' => [
                'type' => 'BearerAccessToken',
            ],
            'logLevel' => 'error',
        ];
        $client = new class ($config) extends BaseClient {
            public function getValidator(): Validator
            {
                return parent::getValidator();
            }
        };
        static::assertInstanceOf(BaseClient::class, $client);
        $test = $client->getHttpClient();
        static::assertInstanceOf(Client::class, $test);
    }

    /**
     * Test `getUrl` method.
     *
     * @return void
     */
    #[CoversMethod(BaseClient::class, 'getUrl')]
    public function testGetUrl(): void
    {
        $config = [
            'auth' => [
                'type' => 'BearerAccessToken',
            ],
            'logLevel' => 'error',
            'url' => 'https://example.com/api/v2',
        ];
        $client = new class ($config) extends BaseClient {
            public function getUrl(string $url): string
            {
                return parent::getUrl($url);
            }
        };
        $url = $client->getUrl('https://example.com/api/objects');
        static::assertSame('https://example.com/api/objects', $url);
        $url = $client->getUrl('/objects');
        static::assertSame('api/v2/objects', $url);
    }

    /**
     * Test `logCall` method.
     *
     * @return void
     */
    #[CoversMethod(BaseClient::class, 'logCall')]
    public function testLogCall(): void
    {
        $config = [
            'auth' => [
                'type' => 'BearerAccessToken',
            ],
            'logLevel' => 'info',
            'url' => 'https://example.com/api/v2',
        ];
        $client = new class ($config) extends BaseClient {
            public function logCall(string $call, string $url, string $payload, Response $response): ?string
            {
                return parent::logCall($call, $url, $payload, $response);
            }
        };
        $response = new Response();
        $payload = '{"data": "test"}';
        $log = $client->logCall('/GET', 'https://example.com', $payload, $response);
        static::assertNull($log);

        // log level error, response
        $config['logLevel'] = 'error';
        $client = new class ($config) extends BaseClient {
            public function logCall(string $call, string $url, string $payload, Response $response): ?string
            {
                return parent::logCall($call, $url, $payload, $response);
            }
        };
        $response = $response->withStatus(200);
        $payload = '{"data": "test"}';
        $log = $client->logCall('/GET', 'https://example.com', $payload, $response);
        static::assertNull($log);

        // log level debug, response ok
        $config['logLevel'] = 'debug';
        $client = new class ($config) extends BaseClient {
            public function logCall(string $call, string $url, string $payload, Response $response): ?string
            {
                return parent::logCall($call, $url, $payload, $response);
            }
        };
        $stream = new Stream('php://memory', 'wb+');
        $stream->write('this is a response body');
        $stream->rewind();
        $response = $response->withStatus(200)->withBody($stream);
        $payload = '{"data": "test"}';
        $log = $client->logCall('/GET', 'https://example.com', $payload, $response);
        static::assertEquals('[OK] API BaseClientTest.php:1 | /GET https://example.com | with status 200: this is a response body - Payload: {"data": "test"}', $log);

        // log level debug, response with error
        $config['logLevel'] = 'debug';
        $client = new class ($config) extends BaseClient {
            public function logCall(string $call, string $url, string $payload, Response $response): ?string
            {
                return parent::logCall($call, $url, $payload, $response);
            }
        };
        $stream = new Stream('php://memory', 'wb+');
        $stream->write('this is a response body for error');
        $stream->rewind();
        $response = $response->withStatus(400)->withBody($stream);
        $payload = '{"data": "test"}';
        $log = $client->logCall('/GET', 'https://example.com', $payload, $response);
        static::assertEquals('[ERROR] API BaseClientTest.php:1 | /GET https://example.com | with status 400: this is a response body for error - Payload: {"data": "test"}', $log);
    }

    /**
     * Data provider for `testGetPostPatchPutDelete` test case.
     *
     * @return array
     */
    public static function getPostPatchPutDeleteProvider(): array
    {
        return [
            'get call' => ['get'],
            'post call' => ['post'],
            'patch call' => ['patch'],
            'put call' => ['put'],
            'delete call' => ['delete'],
        ];
    }

    /**
     * Test `get`, `post`, `patch`, `put`, `delete` methods.
     *
     * @return void
     */
    #[CoversMethod(BaseClient::class, 'get')]
    #[CoversMethod(BaseClient::class, 'post')]
    #[CoversMethod(BaseClient::class, 'patch')]
    #[CoversMethod(BaseClient::class, 'put')]
    #[CoversMethod(BaseClient::class, 'delete')]
    #[CoversMethod(BaseClient::class, 'logCall')]
    #[DataProvider('getPostPatchPutDeleteProvider')]
    public function testGetPostPatchPutDelete(string $method): void
    {
        $config = [
            'auth' => [
                'type' => 'BearerAccessToken',
            ],
            'logLevel' => 'debug',
            'url' => 'https://example.com/api/v2',
        ];
        $client = new class ($config) extends BaseClient {
            public string $lastLog = '';

            public function createClient(): void
            {
                $options = [
                    'host' => 'example.com',
                    'scheme' => 'https',
                    'port' => 443,
                    'path' => '/api/v2',
                ];
                $this->client = new class ($options) extends Client {
                    public function get(string $url, $data = [], array $options = []): Response
                    {
                        $response = new Response();
                        $stream = new Stream('php://memory', 'wb+');
                        $stream->write('this is a response body');
                        $stream->rewind();

                        return $response->withStatus(200)->withBody($stream);
                    }

                    public function post(string $url, $data = [], array $options = []): Response
                    {
                        $response = new Response();
                        $stream = new Stream('php://memory', 'wb+');
                        $stream->write('this is a response body');
                        $stream->rewind();

                        return $response->withStatus(200)->withBody($stream);
                    }

                    public function patch(string $url, $data = [], array $options = []): Response
                    {
                        $response = new Response();
                        $stream = new Stream('php://memory', 'wb+');
                        $stream->write('this is a response body');
                        $stream->rewind();

                        return $response->withStatus(200)->withBody($stream);
                    }

                    public function put(string $url, $data = [], array $options = []): Response
                    {
                        $response = new Response();
                        $stream = new Stream('php://memory', 'wb+');
                        $stream->write('this is a response body');
                        $stream->rewind();

                        return $response->withStatus(200)->withBody($stream);
                    }

                    public function delete(string $url, $data = [], array $options = []): Response
                    {
                        $response = new Response();
                        $stream = new Stream('php://memory', 'wb+');
                        $stream->write('this is a response body');
                        $stream->rewind();

                        return $response->withStatus(200)->withBody($stream);
                    }
                };
            }

            public function logCall(string $call, string $url, string $payload, Response $response): ?string
            {
                $this->lastLog = parent::logCall($call, $url, $payload, $response);

                return $this->lastLog;
            }
        };
        $response = $client->$method('/whatever', ['data' => 'test']);
        static::assertInstanceOf(Response::class, $response);
        static::assertSame(200, $response->getStatusCode());
        $expected = sprintf('[OK] API BaseClientTest.php:2 | /%s api/v2/whatever | with status 200: this is a response body - Payload: {"data":"test"}', strtoupper($method));
        static::assertSame($expected, $client->lastLog);
    }
}
