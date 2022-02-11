<?php
declare(strict_types=1);

/**
 * BEdita, API-first content management framework
 * Copyright 2022 Atlas Srl, ChannelWeb Srl, Chialab Srl
 *
 * This file is part of BEdita: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * See LICENSE.LGPL or <http://gnu.org/licenses/lgpl-3.0.html> for more details.
 */
namespace BEdita\WebTools\Test\TestCase;

use Authorization\AuthorizationService;
use Authorization\Identity as AuthorizationIdentity;
use BEdita\WebTools\Identity;
use Cake\TestSuite\TestCase;

/**
 * {@see BEdita\WebTools\Identity} Test Case
 *
 * @coversDefaultClass \BEdita\WebTools\Identity
 */
class IdentityTest extends TestCase
{
    /**
     * Keep Identity instance.
     *
     * @var \BEdita\WebTools\Identity
     */
    protected $identity = null;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        parent::setUp();

        $data = [
            'id' => 1,
            'roles' => ['admin', 'basic'],
        ];

        $this->identity = new Identity($data);
    }

    /**
     * @inheritDoc
     */
    public function tearDown(): void
    {
        parent::tearDown();

        $this->identity = null;
    }

    /**
     * Test hasRole()
     *
     * @return void
     * @covers ::hasRole()
     */
    public function testHasRole(): void
    {
        static::assertTrue($this->identity->hasRole('admin'));
        static::assertFalse($this->identity->hasRole('manager'));

        $authorizationIdentity = new AuthorizationIdentity($this->createMock(AuthorizationService::class), $this->identity);
        static::assertTrue($authorizationIdentity->hasRole('admin'));
        static::assertFalse($authorizationIdentity->hasRole('manager'));
    }
}
