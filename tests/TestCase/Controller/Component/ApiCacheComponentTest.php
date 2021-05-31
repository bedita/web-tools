<?php
declare(strict_types=1);

namespace BEdita\WebTools\Test\TestCase\Controller\Component;

use BEdita\SDK\BEditaClient;
use BEdita\WebTools\ApiClientProvider;
use BEdita\WebTools\Controller\Component\ApiCacheComponent;
use Cake\Cache\Cache;
use Cake\Controller\ComponentRegistry;
use Cake\Core\Configure;
use Cake\TestSuite\TestCase;

/**
 * {@see \Bedita\WebTools\Controller\Component\ApiCacheComponent} Test Case
 *
 * @coversDefaultClass \Bedita\WebTools\Controller\Component\ApiCacheComponent
 */
class ApiCacheComponentTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Controller\Component\ApiCacheComponent
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
    }

    /**
     * Create GET Request for `testGet` method
     *
     * @return array
     */
    public function createFirstGet(): array
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
    public function createSecondGet(): array
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
     * Cached GET API call test
     *
     * @return void
     * @covers ::get()
     * @covers: cacheKey()
     * @covers: updateCacheKey()
     */
    public function testGet(): void
    {
        $path = '/users/1/roles';
        $query = null;

         // case response empty, with mock
         $apiMockClient = $this->getMockBuilder(BEditaClient::class)
         ->setConstructorArgs([Configure::read('API.apiBaseUrl'), Configure::read('API.apiKey')])
         ->setMethods(['thumbs'])
         ->getMock();
        $response = $this->createFirstGet();
        $apiMockClient->method('get')->willReturn($response);
        ApiClientProvider::setApiClient($apiMockClient);
        $this->ApiCache->get($path, $query);
        // response is cached
        $key = $this->ApiCache->cacheKey($path, $query);
        $expected = $this->createFirstGet();
        $actual = Cache::read($key, '_apicache_');
        static::assertEquals($expected, $actual);

        // response is changed but first get is cached (chron will unvalidate cahce)
        $apiMockClient = $this->getMockBuilder(BEditaClient::class)
            ->setConstructorArgs([Configure::read('API.apiBaseUrl'), Configure::read('API.apiKey')])
            ->setMethods(['thumbs'])
            ->getMock();

        $apiMockClient->method('get')->willReturn($this->createSecondGet());
        ApiClientProvider::setApiClient($apiMockClient);
        $this->ApiCache->get($path, $query);
        $actual = Cache::read($key, '_apicache_');
        $expected = $this->createFirstGet();
        static::assertEquals($expected, $actual);
    }
}
