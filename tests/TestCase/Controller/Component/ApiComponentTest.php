<?php
declare(strict_types=1);

/**
 * BEdita, API-first content management framework
 * Copyright 2020 ChannelWeb Srl, Chialab Srl
 *
 * This file is part of BEdita: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * See LICENSE.LGPL or <http://gnu.org/licenses/lgpl-3.0.html> for more details.
 */
namespace BEdita\WebTools\Test\TestCase\Controller\Component;

use BEdita\SDK\BEditaClient;
use BEdita\SDK\BEditaClientException;
use BEdita\WebTools\ApiClientProvider;
use BEdita\WebTools\Controller\Component\ApiComponent;
use Cake\Controller\ComponentRegistry;
use Cake\TestSuite\TestCase;
use Cake\Utility\Hash;

/**
 * {@see \BEdita\WebTools\Controller\Component\ApiComponent} Test Case
 *
 * @coversDefaultClass \BEdita\WebTools\Controller\Component\ApiComponent
 */
class ApiComponentTest extends TestCase
{
    /**
     * The ApiComponent instance
     *
     * @var \BEdita\WebTools\Controller\Component\ApiComponent
     */
    protected $ApiComponent = null;

    public function setUp(): void
    {
        parent::setUp();

        $this->ApiComponent = new ApiComponent(new ComponentRegistry());
        $this->ApiComponent->setConfig('apiClient', ApiClientProvider::getApiClient());
    }

    /**
     * Data provider for testGetClient
     *
     * @return array
     */
    public function getClientProvider(): array
    {
        return [
            'missingClient' => [
                new \InvalidArgumentException(),
                null,
            ],
            'wrongClient' => [
                new \InvalidArgumentException(),
                'GustavoClient',
            ],
            'ok' => [
                BEditaClient::class,
                ApiClientProvider::getApiClient(),
            ],
        ];
    }

    /**
     * Test for getClient()
     *
     * @param mixed $expected The expected value
     * @param mixed $apiClient The api client for configuration
     * @return void
     *
     * @dataProvider getClientProvider
     * @covers ::getClient()
     */
    public function testGetClient($expected, $apiClient): void
    {
        if ($expected instanceof \Exception) {
            $this->expectException(\InvalidArgumentException::class);
        }

        $this->ApiComponent->setConfig('apiClient', $apiClient);
        $client = $this->ApiComponent->getClient();
        static::assertInstanceOf($expected, $client);
    }

    /**
     * Test for enableExceptions
     *
     * @return void
     *
     * @covers ::enableExceptions()
     */
    public function testEnableExceptions(): void
    {
        $this->ApiComponent->enableExceptions(false);
        static::assertFalse($this->ApiComponent->getConfig('exceptionsEnabled'));

        $this->ApiComponent->enableExceptions(true);
        static::assertTrue($this->ApiComponent->getConfig('exceptionsEnabled'));
    }

    /**
     * Test method proxied to BEditaClient
     *
     * @return void
     *
     * @covers ::__call()
     */
    public function testProxyToApiClientOk(): void
    {
        $response = $this->ApiComponent->getObject(1);
        static::assertEquals('1', Hash::get($response, 'data.id'));
    }

    /**
     * Test that BEditaClientException was thrown.
     *
     * @return void
     *
     * @covers ::__call()
     */
    public function testExceptionThrown(): void
    {
        $this->expectException(BEditaClientException::class);
        $this->ApiComponent->getObject(1000);
    }

    /**
     * Test that BEditaClientException was not thrown and error is populated.
     *
     * @return void
     *
     * @covers ::__call()
     * @covers ::hasError()
     * @covers ::getError()
     */
    public function testError(): void
    {
        $this->ApiComponent->enableExceptions(false)->getObject(1000);
        static::assertTrue($this->ApiComponent->hasError());
        static::assertInstanceOf(BEditaClientException::class, $this->ApiComponent->getError());
    }
}
