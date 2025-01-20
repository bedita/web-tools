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

namespace BEdita\WebTools\Test\TestCase\Identifier;

use BEdita\WebTools\Http\BaseClient;
use Cake\Core\Configure;
use Cake\Http\Client;
use Cake\Http\Client\Response;
use Cake\TestSuite\TestCase;
use Cake\Validation\Validator;

/**
 * {@see \BEdita\WebTools\Http\BaseClient} Test Case
 *
 * @coversDefaultClass \BEdita\WebTools\Http\BaseClient
 */
class BaseClientTest extends TestCase
{
    /**
     * Test constructor against invalid configuration.
     *
     * @return void
     * @covers ::__construct()
     * @covers ::validateConf()
     * @covers ::getValidator()
     * @covers ::createClient()
     */
    public function testInvalidConfig(): void
    {
        $this->expectException(\InvalidArgumentException::class);
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
     * @covers ::__construct()
     * @covers ::validateConf()
     * @covers ::getValidator()
     * @covers ::createClient()
     * @covers ::defaultConfigName()
     * @covers ::getHttpClient()
     */
    public function testBase(): void
    {
        Configure::write('BaseClientTest.php', ['url' => 'https://example.com']);
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
     * @covers ::getUrl()
     */
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
     * @covers ::logCall()
     */
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
            public function logCall(Response $response, string $payload = ''): ?string
            {
                return parent::logCall($response, $payload);
            }
        };
        $response = new Response();
        $payload = '{"data": "test"}';
        $log = $client->logCall($response, $payload);
        static::assertNull($log);

        // log level error, response
        $config['logLevel'] = 'error';
        $client = new class ($config) extends BaseClient {
            public function logCall(Response $response, string $payload = ''): ?string
            {
                return parent::logCall($response, $payload);
            }
        };
        $response = $response->withStatus(200);
        $payload = '{"data": "test"}';
        $log = $client->logCall($response, $payload);
        static::assertNull($log);

        // log level verbose, response with error
        $config['logLevel'] = 'verbose';
        $client = new class ($config) extends BaseClient {
            public function logCall(Response $response, string $payload = ''): ?string
            {
                return parent::logCall($response, $payload);
            }
        };
        $response = $response->withStatus(400);
        $payload = '{"data": "test"}';
        $log = $client->logCall($response, $payload);
        static::assertEquals('error API BaseClientTest.php: with status 400:  - Payload: {"data": "test"}', $log);
    }
}
