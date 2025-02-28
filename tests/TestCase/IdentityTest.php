<?php
declare(strict_types=1);

/**
 * BEdita, API-first content management framework
 * Copyright 2022 Atlas Srl, Chialab Srl
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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;

/**
 * {@see BEdita\WebTools\Identity} Test Case
 */
#[CoversClass(Identity::class)]
class IdentityTest extends TestCase
{
    /**
     * Test hasRole()
     *
     * @return void
     */
    #[CoversMethod(Identity::class, 'hasRole')]
    public function testHasRole(): void
    {
        $data = [
            'id' => 1,
            'roles' => ['admin', 'basic'],
        ];
        $identity = new Identity($data);

        static::assertTrue($identity->hasRole('admin'));
        static::assertFalse($identity->hasRole('manager'));

        $authorizationIdentity = new AuthorizationIdentity($this->createMock(AuthorizationService::class), $identity);
        static::assertTrue($authorizationIdentity->hasRole('admin'));
        static::assertFalse($authorizationIdentity->hasRole('manager'));
    }

    /**
     * Test that hasRole() returns False for identity without role set.
     *
     * @return void
     */
    #[CoversMethod(Identity::class, 'hasRole')]
    public function testHasRoleWithoutRoleInEntity(): void
    {
        $identity = new Identity(['id' => 1]);
        static::assertFalse($identity->hasRole('admin'));
    }
}
