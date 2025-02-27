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
namespace BEdita\WebTools\Test\TestCase\View\Helper;

use BEdita\SDK\BEditaClient;
use BEdita\WebTools\ApiClientProvider;
use BEdita\WebTools\View\Helper\ThumbHelper;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use Cake\Utility\Hash;
use Cake\View\View;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * {@see \BEdita\WebTools\View\Helper\ThumbHelper} Test Case
 *
 * @coversDefaultClass \BEdita\WebTools\View\Helper\ThumbHelper
 */
class ThumbHelperTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \BEdita\WebTools\View\Helper\ThumbHelper
     */
    public $Thumb;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        parent::setUp();

        // set api client in view for helper
        $this->_initApi();

        // create helper
        $this->Thumb = new ThumbHelper(new View());
    }

    /**
     * @inheritDoc
     */
    public function tearDown(): void
    {
        parent::tearDown();

        unset($this->Thumb);
        ApiClientProvider::getApiClient()->setupTokens([]);
    }

    /**
     * Init api client
     *
     * @return void
     */
    private function _initApi(): void
    {
        $apiClient = ApiClientProvider::getApiClient();
        $adminUser = getenv('BEDITA_ADMIN_USR');
        $adminPassword = getenv('BEDITA_ADMIN_PWD');
        $response = $apiClient->authenticate($adminUser, $adminPassword);
        $apiClient->setupTokens($response['meta']);
    }

    /**
     * Create image and media stream for test.
     * Return id
     *
     * @param string $filename the File name.
     * @return int The image ID.
     */
    private function _image($filename = 'test.png'): int
    {
        $apiClient = ApiClientProvider::getApiClient();

        $filepath = sprintf('%s/tests/files/%s', getcwd(), $filename);
        $response = $apiClient->upload($filename, $filepath);

        $streamId = $response['data']['id'];
        $response = $apiClient->get(sprintf('/streams/%s', $streamId));

        $type = 'images';
        $title = 'The test image';
        $attributes = compact('title');
        $data = compact('type', 'attributes');
        $body = compact('data');
        $response = $apiClient->createMediaFromStream($streamId, $type, $body);

        return (int)$response['data']['id'];
    }

    /**
     * Create image and media stream for test.
     * Return id
     *
     * @param string $filename the File name.
     * @return array|null The image Data.
     */
    private function _imageData($filename = 'test.png'): ?array
    {
        $apiClient = ApiClientProvider::getApiClient();

        $filepath = sprintf('%s/tests/files/%s', getcwd(), $filename);
        $response = $apiClient->upload($filename, $filepath);

        $streamId = $response['data']['id'];
        $response = $apiClient->get(sprintf('/streams/%s', $streamId));

        $type = 'images';
        $title = 'The test image';
        $attributes = compact('title');
        $data = compact('type', 'attributes');
        $body = compact('data');
        $response = $apiClient->createMediaFromStream($streamId, $type, $body);
        $response['id'] = (int)$response['data']['id'];

        return $response;
    }

    /**
     * Initialize Thumb Helper test
     *
     * @return void
     * @covers ::initialize()
     */
    public function testInitialize(): void
    {
        $this->Thumb = new ThumbHelper(new View());
        $actual = $this->Thumb->getConfig('cache');
        $expected = 'default';
        static::assertEquals($expected, $actual);
    }

    /**
     * Initialize Thumb Helper test, custom cfg
     *
     * @return void
     * @covers ::initialize()
     */
    public function testInitializeCustomConfig(): void
    {
        $expected = 'dummy';
        Cache::setConfig(
            $expected,
            [
                'engine' => 'File',
                'prefix' => sprintf('%s_', $expected),
                'serialize' => true,
            ]
        );
        $this->Thumb = new ThumbHelper(new View(), ['cache' => $expected]);
        $actual = $this->Thumb->getConfig('cache');
        static::assertEquals($expected, $actual);
    }

    /**
     * Data provider for `testUrl` test case.
     *
     * @return array
     */
    public static function urlProvider(): array
    {
        return [
            'basic thumb default preset' => [
                [
                    'id' => null,
                    'options' => null, // use default preset
                ],
                true,
            ],
            'thumb error, return null' => [
                [
                    'id' => 999999999999999999999999999999999999999999999,
                    'options' => null, // use default preset
                ],
                ThumbHelper::NOT_AVAILABLE,
            ],
        ];
    }

    /**
     * Test `url()` method.
     *
     * @covers ::url()
     * @covers ::status()
     * @param array $input The input array.
     * @param bool $expected The expected boolean.
     * @return void
     */
    #[DataProvider('urlProvider')]
    public function testUrl(array $input, $expected): void
    {
        $id = empty($input['id']) ? $this->_image() : $input['id'];
        $this->Thumb = new ThumbHelper(new View());
        $result = $this->Thumb->url($id, $input['options']);

        if ($expected === true) {
            static::assertNotNull($result);
        } else {
            static::assertEquals($expected, $result);
        }
    }

    /**
     * Test `status()` method.
     *
     * @covers ::status()
     * @param array $input The input array.
     * @param bool $expected The expected boolean.
     * @return void
     */
    #[DataProvider('urlProvider')]
    public function testStatus(array $input, $expected): void
    {
        // case response with api call
        $id = empty($input['id']) ? $this->_image() : $input['id'];
        $this->Thumb = new ThumbHelper(new View());
        $status = $this->Thumb->status($id, $input['options'], $result);

        if ($expected === true) {
            static::assertEquals($status, ThumbHelper::OK);
        } else {
            static::assertNull($result);
            static::assertEquals($status, $expected);
        }
        // case response empty, with mock
        $apiMockClient = $this->getMockBuilder(BEditaClient::class)
            ->setConstructorArgs([Configure::read('API.apiBaseUrl'), Configure::read('API.apiKey')])
            ->onlyMethods(['thumbs'])
            ->getMock();
        $response = null;
        $apiMockClient->method('thumbs')->willReturn($response);
        ApiClientProvider::setApiClient($apiMockClient);
        $result = null;
        $status = $this->Thumb->status(1, null, $result);
        static::assertNull($result);
        static::assertEquals($status, ThumbHelper::NOT_AVAILABLE);
    }

    /**
     * Test `isAcceptable()` method.
     *
     * @covers ::status()
     * @covers ::isAcceptable()
     * @return void
     */
    public function testIsAcceptable(): void
    {
        // case thumb image is acceptable
        $apiMockClient = $this->getMockBuilder(BEditaClient::class)
            ->setConstructorArgs([Configure::read('API.apiBaseUrl'), Configure::read('API.apiKey')])
            ->onlyMethods(['thumbs'])
            ->getMock();
        $response = [
            'meta' => [
                'thumbnails' => [
                    [
                        'ready' => true,
                        'acceptable' => false,
                    ],
                ],
            ],
        ];
        $apiMockClient->method('thumbs')->willReturn($response);
        ApiClientProvider::setApiClient($apiMockClient);
        $result = null;
        $status = $this->Thumb->status(1, null, $result);
        static::assertNull($result);
        static::assertEquals($status, ThumbHelper::NOT_ACCEPTABLE);

        // case thumb image is not acceptable
        $apiMockClient = $this->getMockBuilder(BEditaClient::class)
            ->setConstructorArgs([Configure::read('API.apiBaseUrl'), Configure::read('API.apiKey')])
            ->onlyMethods(['thumbs'])
        ->getMock();
        $url = 'http://...';
        $response = [
            'meta' => [
                'thumbnails' => [
                    [
                        'ready' => true,
                        'acceptable' => true,
                        'url' => $url,
                    ],
                ],
            ],
        ];
        $apiMockClient->method('thumbs')->willReturn($response);
        ApiClientProvider::setApiClient($apiMockClient);
        $result = null;
        $status = $this->Thumb->status(1, null, $result);
        static::assertEquals($result, $url);
        static::assertEquals($status, ThumbHelper::OK);
    }

    /**
     * Test `isReady()` method.
     *
     * @covers ::status()
     * @covers ::isReady()
     * @return void
     */
    public function testIsReady(): void
    {
        // case thumb ready
        $apiMockClient = $this->getMockBuilder(BEditaClient::class)
            ->setConstructorArgs([Configure::read('API.apiBaseUrl'), Configure::read('API.apiKey')])
            ->onlyMethods(['thumbs'])
        ->getMock();
        $response = [
            'meta' => [
                'thumbnails' => [
                    [
                        'ready' => false,
                    ],
                ],
            ],
        ];
        $apiMockClient->method('thumbs')->willReturn($response);
        ApiClientProvider::setApiClient($apiMockClient);
        $result = null;
        $status = $this->Thumb->status(1, null, $result);
        static::assertNull($result);
        static::assertEquals($status, ThumbHelper::NOT_READY);

        // case thumb not ready
        $apiMockClient = $this->getMockBuilder(BEditaClient::class)
            ->setConstructorArgs([Configure::read('API.apiBaseUrl'), Configure::read('API.apiKey')])
            ->onlyMethods(['thumbs'])
        ->getMock();
        $url = 'http://...';
        $response = [
            'meta' => [
                'thumbnails' => [
                    [
                        'ready' => true,
                        'url' => $url,
                    ],
                ],
            ],
        ];
        $apiMockClient->method('thumbs')->willReturn($response);
        ApiClientProvider::setApiClient($apiMockClient);
        $result = null;
        $status = $this->Thumb->status(1, null, $result);
        static::assertEquals($result, $url);
        static::assertEquals($status, ThumbHelper::OK);
    }

    /**
     * Test `hasUrl()` method.
     *
     * @covers ::status()
     * @covers ::hasUrl()
     * @return void
     */
    public function testHasUrl(): void
    {
        // case url not available
        $apiMockClient = $this->getMockBuilder(BEditaClient::class)
            ->setConstructorArgs([Configure::read('API.apiBaseUrl'), Configure::read('API.apiKey')])
            ->onlyMethods(['thumbs'])
        ->getMock();
        $response = [
            'meta' => [
                'thumbnails' => [
                    [
                        'ready' => true,
                    ],
                ],
            ],
        ];
        $apiMockClient->method('thumbs')->willReturn($response);
        ApiClientProvider::setApiClient($apiMockClient);
        $result = null;
        $status = $this->Thumb->status(1, null, $result);
        static::assertNull($result);
        static::assertEquals($status, ThumbHelper::NO_URL);

        // case url available
        $apiMockClient = $this->getMockBuilder(BEditaClient::class)
            ->setConstructorArgs([Configure::read('API.apiBaseUrl'), Configure::read('API.apiKey')])
            ->onlyMethods(['thumbs'])
            ->getMock();
        $url = 'http://...';
        $response = [
            'meta' => [
                'thumbnails' => [
                    [
                        'ready' => true,
                        'url' => $url,
                    ],
                ],
            ],
        ];
        $apiMockClient->method('thumbs')->willReturn($response);
        ApiClientProvider::setApiClient($apiMockClient);
        $result = null;
        $status = $this->Thumb->status(1, null, $result);
        static::assertEquals($result, $url);
        static::assertEquals($status, ThumbHelper::OK);
    }

    /**
     * Test `status()` method with missing input.
     *
     * @covers ::status()
     * @return void
     */
    public function testStatusInput(): void
    {
        $status = $this->Thumb->status(null);
        static::assertEquals($status, ThumbHelper::NOT_ACCEPTABLE);
    }

    /**
     * Test `getUrl()`
     *
     * @covers ::getUrl()
     * @return void
     */
    public function testGetUrl(): void
    {
        //null and []
        $this->Thumb = new ThumbHelper(new View());
        $actual = $this->Thumb->getUrl(null);
        static::assertEquals('', $actual);
        $actual = $this->Thumb->getUrl([]);
        static::assertEquals('', $actual);

        //fake data NOT in cache
        $image = $this->_imageData();
        $expected = $this->Thumb->url($image['id'], []);
        $actual = $this->Thumb->getUrl($image);
        static::assertEquals($expected, $actual);

        //fake data in cache
        $image = $this->_imageData();
        $actual = $this->Thumb->getUrl($image);
        $thumbHash = md5((string)Hash::get($image, 'meta.media_url') . json_encode([]));
        $key = sprintf('%d_%s', $image['id'], $thumbHash);
        Cache::write($key, $actual);
        $expected = $this->Thumb->url($image['id'], []);
        static::assertEquals($expected, $actual);
    }
}
