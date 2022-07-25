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

namespace BEdita\WebTools\Test\TestCase\Identifier;

use ArrayObject;
use BEdita\SDK\BEditaClient;
use BEdita\SDK\BEditaClientException;
use BEdita\WebTools\ApiClientProvider;
use BEdita\WebTools\Identifier\OAuth2Identifier;
use Cake\TestSuite\TestCase;

/**
 * {@see \BEdita\WebTools\Identifier\OAuth2Identifier} Test Case
 *
 * @coversDefaultClass \BEdita\WebTools\Identifier\OAuth2Identifier
 */
class OAuth2IdentifierTest extends TestCase
{
    /**
     * @inheritDoc
     */
    public function tearDown(): void
    {
        parent::tearDown();
        ApiClientProvider::setApiClient(null);
    }

    /**
     * Test `identify` method with successful login.
     *
     * @return void
     * @covers ::identify()
     * @covers ::externalAuth()
     */
    public function testIdentifyOk(): void
    {
        $apiClientMock = $this->getMockBuilder(BEditaClient::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get', 'post'])
            ->getMock();
        $apiClientMock->method('post')->willReturn([
            'meta' => ['jwt' => 'gustavo'],
        ]);
        $apiClientMock->method('get')->willReturn([
            'data' => ['id' => 1],
        ]);

        $identifier = new OAuth2Identifier();
        ApiClientProvider::setApiClient($apiClientMock);

        $identity = $identifier->identify([]);
        $expected = new ArrayObject([
            'id' => 1,
            'tokens' => [
                'jwt' => 'gustavo',
            ],
        ]);
        static::assertEquals($expected, $identity);
    }

    /**
     * Test `identify` method with unsuccessful login.
     *
     * @return void
     * @covers ::identify()
     * @covers ::externalAuth()
     */
    public function testNullIdentify(): void
    {
        $apiClientMock = $this->getMockBuilder(BEditaClient::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['post'])
            ->getMock();
        $apiClientMock->method('post')->willThrowException(
            new BEditaClientException('', 404)
        );

        $identifier = new OAuth2Identifier();
        ApiClientProvider::setApiClient($apiClientMock);

        $identity = $identifier->identify([]);
        static::assertNull($identity);
    }

    /**
     * Test `identify` method with successful signup.
     *
     * @return void
     * @covers ::identify()
     * @covers ::signup()
     * @covers ::signupData()
     */
    public function testOkSignup(): void
    {
        $apiClientMock = $this->getMockBuilder(BEditaClient::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get', 'post'])
            ->getMock();
        $count = 0;
        $apiClientMock->method('post')->willReturnCallback(
            function () use (&$count) {
                if ($count == 0) {
                    $count++;
                    throw new BEditaClientException('', 401);
                }

                return ['meta' => ['jwt' => 'gustavo']];
            }
        );
        $apiClientMock->method('get')->willReturn([
                'data' => ['id' => 1],
        ]);

        $identifier = new OAuth2Identifier([
            'autoSignup' => true,
            'providers' => [
                'gustavo' => [
                    'map' => ['id' => 'id'],
                ],
            ],
        ]);
        ApiClientProvider::setApiClient($apiClientMock);

        $identity = $identifier->identify(['auth_provider' => 'gustavo']);
        $expected = new ArrayObject([
            'id' => 1,
            'tokens' => [
                'jwt' => 'gustavo',
            ],
        ]);
        static::assertEquals($expected, $identity);
    }

    /**
     * Test `identify` method with unsuccessful signup.
     *
     * @return void
     * @covers ::identify()
     * @covers ::signup()
     * @covers ::signupData()
     */
    public function testFailSignup(): void
    {
        $apiClientMock = $this->getMockBuilder(BEditaClient::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['post'])
            ->getMock();
        $apiClientMock->method('post')->willThrowException(new BEditaClientException('', 401));

        $identifier = new OAuth2Identifier([
            'autoSignup' => true,
            'providers' => [
                'gustavo' => [
                    'map' => ['id' => 'id'],
                ],
            ],
        ]);
        ApiClientProvider::setApiClient($apiClientMock);

        $identity = $identifier->identify(['auth_provider' => 'gustavo']);
        static::assertNull($identity);
    }
}
