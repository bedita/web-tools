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
namespace BEdita\WebTools\Test\TestCase\View\Helper;

use BadMethodCallException;
use BEdita\WebTools\Identity;
use BEdita\WebTools\View\Helper\IdentityHelper;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;
use Cake\View\View;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;

/**
 * {@see BEdita\WebTools\View\Helper\IdentityHelper} Test Case
 */
#[CoversClass(IdentityHelper::class)]
#[CoversMethod(IdentityHelper::class, '__call')]
class IdentityHelperTest extends TestCase
{
    /**
     * Keep Identity instance.
     *
     * @var \BEdita\WebTools\Identity|null
     */
    protected ?Identity $identity = null;

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
     * Test that delegating a configured method works as expected.
     *
     * @return void
     */
    public function testDelgateOk(): void
    {
        $request = (new ServerRequest())->withAttribute('identity', $this->identity);
        $View = new View($request);
        $identityHelper = new IdentityHelper($View);

        static::assertTrue($identityHelper->hasRole('admin'));
        static::assertFalse($identityHelper->hasRole('manager'));
    }

    /**
     * Test that a `BadMethodCallException` is thrown calling method without identity.
     *
     * @return void
     */
    public function testDelegateNoIdentity(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Cannot call `hasRole` on stored identity since it is not an object.');

        $request = (new ServerRequest())->withAttribute('identity', null);
        $View = new View($request);
        $identityHelper = new IdentityHelper($View);
        $identityHelper->hasRole('admin');
    }

    /**
     * Test that a `BadMethodCallException` is thrown calling not delegated method.
     *
     * @return void
     */
    public function testDelegateBadDelegateMethod(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Cannot call `fakeMethod`. Make sure to add it to `delegateMethods`.');

        $request = (new ServerRequest())->withAttribute('identity', $this->identity);
        $View = new View($request);
        $identityHelper = new IdentityHelper($View);
        $identityHelper->fakeMethod();
    }
}
