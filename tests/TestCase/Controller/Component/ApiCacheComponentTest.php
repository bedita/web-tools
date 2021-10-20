<?php
declare(strict_types=1);

/**
 * BEdita, API-first content management framework
 * Copyright 2021 ChannelWeb Srl, Chialab Srl
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
use BEdita\WebTools\ApiClientProvider;
use BEdita\WebTools\Controller\Component\ApiCacheComponent;
use Cake\Cache\Cache;
use Cake\Controller\ComponentRegistry;
use Cake\Core\Configure;
use Cake\TestSuite\TestCase;

/**
 * {@see \BEdita\WebTools\Controller\Component\ApiCacheComponent} Test Case
 *
 * @coversDefaultClass \Bedita\WebTools\Controller\Component\ApiCacheComponent
 */
class ApiCacheComponentTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \BEdita\WebTools\Controller\Component\ApiCacheComponent
     */
    public $ApiCache;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $registry = new ComponentRegistry();
        $this->ApiCache = new ApiCacheComponent($registry);
    }

    /**
     * @inheritDoc
     */
    public function tearDown(): void
    {
        parent::tearDown();

        ApiClientProvider::setApiClient(null);
    }

    /**
     * Initialize Api Cache component test
     *
     * @return void
     * @covers ::initialize()
     */
    public function testInitialize(): void
    {
        //default config
        $expected = '_apicache_';
        Cache::setConfig(
            $expected,
            [
                'engine' => 'File',
                'prefix' => sprintf('%s_', $expected),
                'serialize' => true,
            ]
        );
        $registry = new ComponentRegistry();
        $this->ApiCache = new ApiCacheComponent($registry);
        $actual = $this->ApiCache->getConfig('cache');
        static::assertEquals($expected, $actual);
        Cache::drop($expected);
    }

    /**
     * Initialize Api Cache component test with custom config
     *
     * @return void
     * @covers ::initialize()
     */
    public function testInitializeCustomConfig(): void
    {
         // custom config
         $expected = 'cfapicache';
         Cache::setConfig(
             $expected,
             [
                 'engine' => 'File',
                 'prefix' => sprintf('%s_', $expected),
                 'serialize' => true,
             ]
         );
         $registry = new ComponentRegistry();
         $this->ApiCache = new ApiCacheComponent($registry, ['cache' => $expected]);
         $actual = $this->ApiCache->getConfig('cache');
         static::assertEquals($expected, $actual);
         Cache::drop($expected);
    }

    /**
     * Create GET Request for `testGet` method
     *
     * @return array
     */
    protected function createFirstGet(): array
    {
        return [
            'data' => [
                0 => [
                'id' => '1',
                'type' => 'roles',
                'attributes' => [
                    'name' => 'admin',
                    'description' => 'Administrators role',
                    ],
                'meta' => [
                    'unchangeable' => true,
                    'created' => '2021-05-28T14:36:44+00:00',
                    'modified' => '2021-05-28T14:36:44+00:00',
                    ],
                'links' => [
                    'self' => 'http://localhost:8090/roles/1',
                    ],
                'relationships' => [
                    'users' => [
                        'links' => [
                            'related' => 'http://localhost:8090/roles/1/users',
                            'self' => 'http://localhost:8090/roles/1/relationships/users',
                            ],
                        ],
                    ],
                ],
            ],
            'links' => [
              'available' => 'http://localhost:8090/roles',
              'self' => 'http://localhost:8090/users/1/roles',
              'home' => 'http://localhost:8090/home',
              'first' => 'http://localhost:8090/users/1/roles',
              'last' => 'http://localhost:8090/users/1/roles',
              'prev' => null,
              'next' => null,
            ],
            'meta' => [
              'pagination' => [
                'count' => 1,
                'page' => 1,
                'page_count' => 1,
                'page_items' => 1,
                'page_size' => 20,
              ],
              'schema' => [
                'roles' => [
                  '$id' => 'http://localhost:8090/model/schema/roles',
                  'revision' => '734553033',
                ],
                ],
            ],
        ];
    }

    /**
     * Create GET Request Changed for `testGet` method that have different response 'USER ROLE'
     *
     * @return array
     */
    protected function createSecondGet(): array
    {
        return [
            'data' => [
                0 => [
                'id' => '1',
                'type' => 'roles',
                'attributes' => [
                    'name' => 'user',
                    'description' => 'User role',
                    ],
                'meta' => [
                    'unchangeable' => true,
                    'created' => '2021-05-28T14:36:44+00:00',
                    'modified' => '2021-05-28T14:36:44+00:00',
                    ],
                'links' => [
                    'self' => 'http://localhost:8090/roles/1',
                    ],
                'relationships' => [
                    'users' => [
                        'links' => [
                            'related' => 'http://localhost:8090/roles/1/users',
                            'self' => 'http://localhost:8090/roles/1/relationships/users',
                            ],
                        ],
                    ],
                ],
            ],
            'links' => [
              'available' => 'http://localhost:8090/roles',
              'self' => 'http://localhost:8090/users/1/roles',
              'home' => 'http://localhost:8090/home',
              'first' => 'http://localhost:8090/users/1/roles',
              'last' => 'http://localhost:8090/users/1/roles',
              'prev' => null,
              'next' => null,
            ],
            'meta' => [
              'pagination' => [
                'count' => 1,
                'page' => 1,
                'page_count' => 1,
                'page_items' => 1,
                'page_size' => 20,
              ],
              'schema' => [
                'roles' => [
                  '$id' => 'http://localhost:8090/model/schema/roles',
                  'revision' => '734553033',
                ],
                ],
            ],
        ];
    }

    /**
     * Setup mocked API client
     *
     * @return void
     */
    protected function setupClient(array $response): void
    {
        $apiMockClient = $this->getMockBuilder(BEditaClient::class)
            ->setConstructorArgs([Configure::read('API.apiBaseUrl'), Configure::read('API.apiKey')])
            ->onlyMethods(['get'])
            ->getMock();

        $apiMockClient->method('get')->willReturn($response);
        ApiClientProvider::setApiClient($apiMockClient);
    }

    /**
     * Cached GET API call test
     *
     * @return void
     * @covers ::cacheKey()
     * @covers ::readIndex()
     * @covers ::updateCacheIndex()
     * @covers ::get()
     */
    public function testGet(): void
    {
        $path = '/users/1/roles';
        $query = null;
        $key = sprintf('_users_1_roles_%s', md5(json_encode($query)));

        // case response empty, with mock
        $this->setupClient($this->createFirstGet());
        $this->ApiCache->get($path, $query);
        // check response is cached
        $expected = $this->createFirstGet();
        $actual = Cache::read($key);
        static::assertEquals($expected, $actual);

        // response is changed but first get is cached (another script will invalidate cache)
        $this->setupClient($this->createSecondGet());
        $this->ApiCache->get($path, $query);
        $actual = Cache::read($key);
        $expected = $this->createFirstGet();
        static::assertEquals($expected, $actual);
    }

    /**
     * Test cache API index
     *
     * @return void
     * @covers ::updateCacheIndex()
     * @covers ::readIndex()
     */
    public function testIndex(): void
    {
        $path = '/users/1/roles';
        $query = null;
        $key = sprintf('_users_1_roles_%s', md5(json_encode($query)));

        Cache::clearAll();
        $this->setupClient($this->createFirstGet());
        $this->ApiCache->get($path, $query);

        $idx = array_keys(array_filter((array)Cache::read('index')));
        static::assertEquals([$key], $idx);

        // empty 'index' key and test it is rebuilt on next ApiCache::get()
        Cache::write('index', []);
        $this->setupClient($this->createSecondGet());
        $this->ApiCache = new ApiCacheComponent(new ComponentRegistry());
        $this->ApiCache->get($path, $query);
        $idx = array_keys(array_filter((array)Cache::read('index')));
        static::assertEquals([$key], $idx);
    }
}
