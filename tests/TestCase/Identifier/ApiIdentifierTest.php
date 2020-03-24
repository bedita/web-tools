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

namespace BEdita\WebTools\Test\TestCase\Identifier;

use BEdita\SDK\BEditaClient;
use BEdita\WebTools\ApiClientProvider;
use BEdita\WebTools\Identifier\ApiIdentifier;
use Cake\TestSuite\TestCase;
use Cake\Utility\Hash;

/**
 * {@see \BEdita\WebTools\Identifier\ApiIdentifier} Test Case
 *
 * @coversDefaultClass \BEdita\WebTools\Identifier\ApiIdentifier
 */
class ApiIdentifierTest extends TestCase
{
    /**
     * Username to create.
     */
    protected const USER = 'gustavo';

    /**
     * Role to create.
     */
    protected const ROLE = 'assistant';

    /**
     * The API client instance.
     *
     * @var \BEdita\SDK\BEditaClient
     */
    protected $apiClient = null;

    /**
     * The created user data.
     *
     * @var array
     */
    protected $user = null;

    /**
     * The created role data.
     *
     * @var array
     */
    protected $role = null;

    /**
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        $this->apiClient = ApiClientProvider::getApiClient();
        $response = $this->apiClient->authenticate(getenv('BEDITA_ADMIN_USR'), getenv('BEDITA_ADMIN_PWD'));
        $this->apiClient->setupTokens($response['meta']);
    }

    /**
     * {@inheritDoc}
     */
    public function tearDown(): void
    {
        $roleId = Hash::get((array)$this->role, 'data.id');
        $userId = Hash::get((array)$this->user, 'data.id');
        if ($roleId) {
            $this->apiClient->delete(sprintf('/roles/%s', $roleId));
        }
        if ($userId) {
            $this->apiClient->deleteObject($userId, 'users');
            $this->apiClient->delete(sprintf('/trash/%s', $userId));
        }

        $this->apiClient = null;
        $this->user = null;
        $this->role = null;
    }

    /**
     * Create user, role linked them.
     *
     * @param string $username The username
     * @param string $role The role name
     * @return void
     */
    protected function createUserAndRole(string $username, string $role): void
    {
        $this->user = $this->apiClient->saveObject('users', [
            'username' => $username,
            'password' => 'xyz',
        ]);
        $this->role = $this->apiClient->save('roles', ['name' => $role]);
        $this->apiClient->addRelated(
            Hash::get($this->user, 'data.id'),
            'users',
            'roles',
            [
                [
                    'type' => 'roles',
                    'id' => Hash::get($this->role, 'data.id'),
                ],
            ]
        );
    }

    /**
     * Test missing data for identifier.
     *
     * @return void
     *
     * @covers ::identify()
     */
    public function testIdentifyMissingData(): void
    {
        $this->createUserAndRole(static::USER, static::ROLE);
        $identifier = new ApiIdentifier();

        $identity = $identifier->identify([]);
        static::assertNull($identity);
    }

    /**
     * Test authentication failure.
     *
     * @return void
     *
     * @covers ::identify()
     * @covers ::setError()
     */
    public function testIdentifyAuthenticationFails(): void
    {
        $this->createUserAndRole(static::USER, static::ROLE);
        $identifier = new ApiIdentifier();

        $identity = $identifier->identify([
            ApiIdentifier::CREDENTIAL_USERNAME => 'gianni',
            ApiIdentifier::CREDENTIAL_PASSWORD => 'box',
        ]);

        static::assertNull($identity);
        static::assertEquals('Invalid username or password', current($identifier->getErrors()));
    }

    /**
     * Test authentication ok.
     *
     * @return {void}
     *
     * @covers ::identify()
     */
    public function testIdentifyCorrect(): void
    {
        $this->createUserAndRole(static::USER, static::ROLE);
        $identifier = new ApiIdentifier();

        $identity = $identifier->identify([
            ApiIdentifier::CREDENTIAL_USERNAME => static::USER,
            ApiIdentifier::CREDENTIAL_PASSWORD => 'xyz',
        ]);

        static::assertNotEmpty($identity);
        static::assertArrayHasKey('tokens', $identity);
        static::assertArrayHasKey('roles', $identity);
        static::assertIsArray($identity['roles']);
        static::assertContains(static::ROLE, $identity['roles']);
    }

    /**
     * Test that if missing `meta` from response then identification fails.
     *
     * @return void
     *
     * @covers ::identify()
     */
    public function testMissingMetaFromResponse(): void
    {
        $apiClient = new class('mockUrl') extends BEditaClient {

            public function __construct(string $apiUrl, ?string $apiKey = null, array $tokens = [])
            {
            }

            public function authenticate(string $username, string $password): ?array
            {
                return [];
            }

        };

        ApiClientProvider::setApiClient($apiClient);
        $identifier = new ApiIdentifier();

        $identity = $identifier->identify([
            ApiIdentifier::CREDENTIAL_USERNAME => static::USER,
            ApiIdentifier::CREDENTIAL_PASSWORD => 'xyz',
        ]);

        static::assertNull($identity);
        static::assertEquals('Invalid username or password', current($identifier->getErrors()));
    }
}
