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
namespace BEdita\WebTools\Test\TestCase\Controller;

use BEdita\SDK\BEditaClient;
use BEdita\WebTools\ApiClientProvider;
use BEdita\WebTools\Controller\ApiProxyTrait;
use Cake\Controller\Controller;
use Cake\Http\ServerRequest;
use Cake\Routing\Router;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use Cake\Utility\Hash;

/**
 * ApiProxyTraitTest class
 *
 * {@see \BEdita\WebTools\Controller\ApiProxyTrait} Test Case
 *
 * @coversDefaultClass \BEdita\WebTools\Controller\ApiProxyTrait
 */
class ApiProxyTraitTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Instance of BEditaClient
     *
     * @var \BEdita\SDK\BEditaClient
     */
    protected $apiClient = null;

    /**
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->apiClient = ApiClientProvider::getApiClient();
        $response = $this->apiClient->authenticate(env('BEDITA_ADMIN_USR'), env('BEDITA_ADMIN_PWD'));
        $this->apiClient->setupTokens($response['meta']);
    }

    /**
     * {@inheritDoc}
     */
    public function tearDown(): void
    {
        parent::tearDown();

        $this->apiClient = null;
    }

    /**
     * Get base URL.
     *
     * @return string
     */
    protected function getBaseUrl(): string
    {
        return Router::url('/', true);
    }

    /**
     * Test get() method
     *
     * @return void
     *
     * @covers ::initialize()
     * @covers ::get()
     * @covers ::setBaseUrl()
     * @covers ::apiRequest()
     * @covers ::maskResponseLinks()
     * @covers ::maskMultiLinks()
     * @covers ::maskLinks()
     */
    public function testGet(): void
    {
        $this->get('/api/users/1');
        $this->assertResponseOk();
        $this->assertContentType('application/json');
        $data = $this->viewVariable('data');
        $links = $this->viewVariable('links');
        $meta = $this->viewVariable('meta');
        static::assertNotEmpty($data);
        static::assertNotEmpty($links);
        static::assertNotEmpty($meta);
        static::assertEquals('1', Hash::get($data, 'id'));

        $response = json_decode((string)$this->_response, true);
        static::assertArrayHasKey('data', $response);
        static::assertArrayHasKey('links', $response);
        static::assertArrayHasKey('meta', $response);

        $baseUrl = $this->getBaseUrl();
        foreach ($response['links'] as $link) {
            static::assertStringContainsString($baseUrl, $link);
        }

        foreach (Hash::extract($response, 'data.relationships.{s}.links') as $link) {
            static::assertStringContainsString($baseUrl, $link);
        }
    }

    /**
     * Test non found error proxied from API.
     *
     * @return void
     *
     * @covers ::get()
     * @covers ::apiRequest()
     * @covers ::handleError()
     */
    public function testNotFoundError(): void
    {
        $this->get('/api/users/1000');
        $this->assertResponseError();
        $this->assertContentType('application/json');
        $error = $this->viewVariable('error');
        static::assertNotEmpty($error);

        $response = json_decode((string)$this->_response, true);
        static::assertArrayHasKey('error', $response);
        static::assertArrayHasKey('status', $response['error']);
        static::assertArrayHasKey('title', $response['error']);
    }

    /**
     * Test that masking links with value searched equal to string works.
     *
     * @return void
     *
     * @covers ::maskLinks()
     */
    public function testMaskLinksString(): void
    {
        $this->get('/api/model/schema/users');
        $this->assertResponseOk();
        $this->assertContentType('application/json');
        $response = json_decode((string)$this->_response, true);
        static::assertStringContainsString($this->getBaseUrl(), Hash::get($response, '$id'));
    }

    /**
     * Test that getting a list of objects the relationships links are masked.
     *
     * @return void
     *
     * @covers ::maskResponseLinks()
     */
    public function testMaskRelationshipsLinksGettingList(): void
    {
        $this->get('/api/users');
        $this->assertResponseOk();
        $this->assertContentType('application/json');
        $response = json_decode((string)$this->_response, true);

        foreach (Hash::extract($response, 'data.{n}.relationships.{s}.links.{s}') as $link) {
            static::assertStringContainsString($this->getBaseUrl(), $link);
        }
    }

    /**
     * Test that getting /home the resources links are masked.
     *
     * @return void
     *
     * @covers ::maskResponseLinks()
     */
    public function testMaskResourcesGettingHome(): void
    {
        $this->get('/api/home');
        $this->assertResponseOk();
        $this->assertContentType('application/json');
        $response = json_decode((string)$this->_response, true);

        foreach (Hash::extract($response, 'meta.resources.{s}.href') as $link) {
            static::assertStringContainsString($this->getBaseUrl(), $link);
        }
    }

    /**
     * Test that an exception different from BEditaClientException throws in BEditaClient request
     * is correctly handled
     *
     * @return void
     *
     * @covers ::handleError()
     */
    public function testNotBEditaClientException(): void
    {
        $controller = new class (new ServerRequest()) extends Controller {
            use ApiProxyTrait;

            public function setApiCLient($apiClient)
            {
                $this->apiClient = $apiClient;
            }

            protected function setBaseUrl($path): void
            {
                $this->baseUrl = '/';
            }
        };

        $apiClientMock = $this->getMockBuilder(BEditaClient::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();

        $apiClientMock->method('get')->willThrowException(new \LogicException('Broken'));

        $controller->setApiCLient($apiClientMock);
        $controller->get('/gustavo');
        $error = $controller->viewBuilder()->getVar('error');

        static::assertArrayHasKey('status', $error);
        static::assertArrayHasKey('title', $error);
        static::assertEquals('500', $error['status']);
        static::assertEquals('Broken', $error['title']);
    }
}
