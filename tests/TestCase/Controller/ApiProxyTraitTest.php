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
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;

/**
 * ApiProxyTraitTest class
 *
 * {@see \BEdita\WebTools\Controller\ApiProxyTrait} Test Case
 */
#[CoversClass(ApiProxyTrait::class)]
#[CoversMethod(ApiProxyTrait::class, 'apiRequest')]
#[CoversMethod(ApiProxyTrait::class, 'delete')]
#[CoversMethod(ApiProxyTrait::class, 'get')]
#[CoversMethod(ApiProxyTrait::class, 'handleError')]
#[CoversMethod(ApiProxyTrait::class, 'initialize')]
#[CoversMethod(ApiProxyTrait::class, 'maskLinks')]
#[CoversMethod(ApiProxyTrait::class, 'maskMultiLinks')]
#[CoversMethod(ApiProxyTrait::class, 'maskResponseLinks')]
#[CoversMethod(ApiProxyTrait::class, 'patch')]
#[CoversMethod(ApiProxyTrait::class, 'post')]
#[CoversMethod(ApiProxyTrait::class, 'setBaseUrl')]

class ApiProxyTraitTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Instance of BEditaClient
     *
     * @var \BEdita\SDK\BEditaClient|null
     */
    protected ?BEditaClient $apiClient = null;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->apiClient = ApiClientProvider::getApiClient();
        $response = $this->apiClient->authenticate(env('BEDITA_ADMIN_USR'), env('BEDITA_ADMIN_PWD'));
        $this->apiClient->setupTokens($response['meta']);
    }

    /**
     * @inheritDoc
     */
    public function tearDown(): void
    {
        parent::tearDown();

        $this->apiClient->setupTokens([]);
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
     * Test that a request with a wrong method raises MethodNotAllowedException.
     *
     * @return void
     */
    public function testMethodNotAllowedException(): void
    {
        $t = new class (new ServerRequest()) extends Controller {
            use ApiProxyTrait {
                apiRequest as public;
            }
        };
        $t->apiRequest(['method' => 'PPOOSSTT', 'path' => '/']);
        $error = $t->viewBuilder()->getVar('error');
        static::assertTrue(in_array($error['status'], ['405', '500']));
        static::assertEquals('Method Not Allowed', $error['title']);
    }

    /**
     * Test get() method
     *
     * @return void
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

        $varNotSerialized = $this->viewVariable('varNotSerialized');
        static::assertTrue($varNotSerialized);

        $response = json_decode((string)$this->_response, true);
        static::assertArrayHasKey('data', $response);
        static::assertArrayHasKey('links', $response);
        static::assertArrayHasKey('meta', $response);
        static::assertArrayNotHasKey('varNotSerialized', $response);

        $baseUrl = $this->getBaseUrl();
        foreach ($response['links'] as $link) {
            static::assertStringStartsWith($baseUrl, $link);
        }

        $relationshipsLinks = (array)Hash::extract($response, 'data.relationships.{s}.links.{s}');
        static::assertNotEmpty($relationshipsLinks);

        foreach ($relationshipsLinks as $link) {
            static::assertStringStartsWith($baseUrl, $link);
        }
    }

    /**
     * Test non found error proxied from API.
     *
     * @return void
     */
    public function testNotFoundError(): void
    {
        $this->get('/api/users/1000');
        $this->assertResponseError();
        $this->assertContentType('application/json');
        $error = $this->viewVariable('error');
        static::assertNotEmpty($error);
        $this->assertResponseCode(404);

        $response = json_decode((string)$this->_response, true);
        static::assertArrayHasKey('error', $response);
        static::assertArrayHasKey('status', $response['error']);
        static::assertArrayHasKey('title', $response['error']);
    }

    /**
     * Test that masking links with value searched equal to string works.
     *
     * @return void
     */
    public function testMaskLinksString(): void
    {
        $this->get('/api/model/schema/users');
        $this->assertResponseOk();
        $this->assertContentType('application/json');
        $response = json_decode((string)$this->_response, true);
        static::assertStringStartsWith($this->getBaseUrl(), Hash::get($response, '$id'));
    }

    /**
     * Test that getting a list of objects the relationships links are masked.
     *
     * @return void
     */
    public function testMaskRelationshipsLinksGettingList(): void
    {
        $this->get('/api/users');
        $this->assertResponseOk();
        $this->assertContentType('application/json');
        $response = json_decode((string)$this->_response, true);

        $relationshipsLinks = (array)Hash::extract($response, 'data.{n}.relationships.{s}.links.{s}');
        static::assertNotEmpty($relationshipsLinks);

        foreach ($relationshipsLinks as $link) {
            static::assertStringStartsWith($this->getBaseUrl(), $link);
        }
    }

    /**
     * Test that getting /home the resources links are masked.
     *
     * @return void
     */
    public function testMaskResourcesGettingHome(): void
    {
        $this->get('/api/home');
        $this->assertResponseOk();
        $this->assertContentType('application/json');
        $response = json_decode((string)$this->_response, true);

        foreach (Hash::extract($response, 'meta.resources.{s}.href') as $link) {
            static::assertStringStartsWith($this->getBaseUrl(), $link);
        }
    }

    /**
     * Test that an exception different from BEditaClientException throws in BEditaClient request
     * is correctly handled
     *
     * @return void
     */
    public function testNotBEditaClientException(): void
    {
        $controller = new class (new ServerRequest()) extends Controller {
            use ApiProxyTrait;

            public function setApiClient($apiClient)
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
            ->onlyMethods(['get'])
            ->getMock();

        $apiClientMock->method('get')->willThrowException(new LogicException('Broken'));

        $controller->setApiClient($apiClientMock);
        $controller->get('/gustavo');
        $error = $controller->viewBuilder()->getVar('error');

        static::assertEquals(500, $controller->getResponse()->getStatusCode());
        static::assertArrayHasKey('status', $error);
        static::assertArrayHasKey('title', $error);
        static::assertEquals('500', $error['status']);
        static::assertEquals('Broken', $error['title']);
    }

    /**
     * Test that if BEditaClient return null the response has empty body.
     *
     * @return void
     */
    public function testNullResponseFromBEditaClient(): void
    {
        $controller = new class (new ServerRequest()) extends Controller {
            use ApiProxyTrait;

            public function setApiClient($apiClient)
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
            ->onlyMethods(['get'])
            ->getMock();

        $apiClientMock->method('get')->willReturn(null);

        $controller->setApiClient($apiClientMock);
        $controller->get('/gustavo');

        $body = (string)$controller->getResponse()->getBody();
        static::assertEmpty($body);
    }

    /**
     * Test that if path was unexpected an error 400 Bad Request was sent.
     *
     * @return void
     */
    public function testErrorIfPathNotFound(): void
    {
        $controller = new class (new ServerRequest(['url' => '/api/users'])) extends Controller {
            use ApiProxyTrait {
                 setBaseUrl as setBaseUrlTrait;
            }

            protected function setBaseUrl($path): void
            {
                $path = '/injected/path';

                $this->setBaseUrlTrait($path);
            }
        };

        $controller->get('/users');
        $error = $controller->viewBuilder()->getVar('error');

        static::assertEquals(400, $controller->getResponse()->getStatusCode());
        static::assertArrayHasKey('status', $error);
        static::assertArrayHasKey('title', $error);
        static::assertEquals('400', $error['status']);
        static::assertEquals('Path not found in request', $error['title']);
    }

    /**
     * Test that url is urlencoded the baseUrl is found.
     *
     * @return void
     */
    public function testMatchUrlEncodedPath(): void
    {
        $controller = new class (new ServerRequest(['url' => '/api/space%20here'])) extends Controller {
            use ApiProxyTrait;

            public function get($path): void
            {
                $this->setBaseUrl($path);
                $this->set('baseUrl', $this->baseUrl);
            }
        };

        $controller->get('/space here');
        $baseUrl = $controller->viewBuilder()->getVar('baseUrl');
        static::assertEquals('/api', $baseUrl);
    }

    /**
     * Test POST request
     *
     * @return void
     */
    public function testPost(): void
    {
        $this->post('/api/documents', [
            'data' => [
                'type' => 'documents',
                'attributes' => [
                    'title' => 'The Doc',
                ],
            ],
        ]);

        $this->assertResponseOk();
        $this->assertContentType('application/json');
        $response = json_decode((string)$this->_response, true);
        static::assertArrayHasKey('data', $response);
        static::assertArrayHasKey('links', $response);
        static::assertArrayHasKey('meta', $response);
        static::assertEquals('The Doc', Hash::get($response, 'data.attributes.title'));
    }

    /**
     * Test PATCH request
     *
     * @return void
     */
    public function testPatch(): void
    {
        $data = [
            'type' => 'documents',
            'attributes' => [
                'title' => 'new doc',
            ],
        ];
        $this->post('/api/documents', compact('data'));
        $this->assertResponseOk();

        $response = json_decode((string)$this->_response, true);
        $id = Hash::get($response, 'data.id');
        $data['id'] = $id;
        $data['attributes']['title'] = 'new doc title';

        Router::resetRoutes();

        $this->patch('/api/documents/' . $id, compact('data'));
        $this->assertResponseOk();
        $response = json_decode((string)$this->_response, true);
        static::assertArrayHasKey('data', $response);
        static::assertArrayHasKey('links', $response);
        static::assertArrayHasKey('meta', $response);

        static::assertEquals($data['attributes']['title'], Hash::get($response, 'data.attributes.title'));
    }

    /**
     * Test DELETE request
     *
     * @return void
     */
    public function testDelete(): void
    {
        $data = [
            'type' => 'documents',
            'attributes' => [
                'title' => 'new doc',
            ],
        ];
        $this->post('/api/documents', compact('data'));
        $this->assertResponseOk();

        Router::resetRoutes();

        $response = json_decode((string)$this->_response, true);
        $id = Hash::get($response, 'data.id');
        $this->delete('/api/documents/' . $id);
        $this->assertResponseOk();
        $response = json_decode((string)$this->_response, true);
        static::assertEmpty($response);
    }

    /**
     * Test that create a new object, modify it and delete it.
     *
     * @return void
     */
    public function testMulti(): void
    {
        $this->post('/api/documents', ['data' => [
            'type' => 'documents',
            'attributes' => [
                'title' => 'new doc',
            ],
        ]]);
        $this->assertResponseOk();

        Router::resetRoutes();

        $response = json_decode((string)$this->_response, true);
        $id = Hash::get($response, 'data.id');
        $this->patch('/api/documents/' . $id, ['data' => [
            'type' => 'documents',
            'id' => $id,
            'attributes' => [
                'title' => 'new doc title',
            ],
        ]]);
        $this->assertResponseOk();

        Router::resetRoutes();

        $this->get('/api/documents/' . $id);
        $response = json_decode((string)$this->_response, true);
        $this->assertEquals('new doc title', (string)Hash::get($response, 'data.attributes.title'));
        $this->assertResponseOk();

        Router::resetRoutes();

        $this->delete('/api/documents/' . $id);
        $this->assertResponseOk();
        $response = json_decode((string)$this->_response, true);
        $this->assertEmpty($response);

        Router::resetRoutes();

        $this->get('/api/documents/' . $id);
        $this->assertResponseError();
    }
}
