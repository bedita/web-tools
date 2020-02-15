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
namespace BEdita\WebTools\Test\TestCase;

use BEdita\WebTools\ApiClientProvider;
use Cake\TestSuite\TestCase;

/**
 * {@see BEdita\WebTools\ApiClientProvider} Test Case
 *
 * @coversDefaultClass \BEdita\WebTools\ApiClientProvider
 */
class ApiClientProviderTest extends TestCase
{
    /**
     * Test `ApiClientProvider` methods
     *
     * @return void
     */
    public function testApiClient(): void
    {
        ApiClientProvider::setApiClient(null);
        // test create
        $client = ApiClientProvider::getApiClient();
        static::assertNotEmpty($client);
        // test use created
        $client = ApiClientProvider::getApiClient();
        static::assertNotEmpty($client);
    }

    /**
     * Test log configuration
     *
     * @return void
     */
    public function testLogConfig(): void
    {
        $options = [
            'Log' => [
                'log_file' => 'my.log',
            ],
        ];
        $client = ApiClientProvider::getApiClient($options);
        static::assertNotEmpty($client);
        static::assertNotEmpty($client->getLogger());
    }

    /**
     * Test empty log configuration
     *
     * @return void
     */
    public function testLogEmpty(): void
    {
        ApiClientProvider::setApiClient(null);
        // test create
        $client = ApiClientProvider::getApiClient();
        static::assertNotEmpty($client);
        static::assertNull($client->getLogger());
    }
}
