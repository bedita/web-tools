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

use Cake\TestSuite\TestCase;
use ReflectionClass;

/**
 * {@see BEdita\WebTools\SingletonTrait} Test Case
 *
 * @covers \BEdita\WebTools\SingletonTrait
 */
class SingletonTraitTest extends TestCase
{
    /**
     * Assert that the class cannot be instantiated.
     *
     * @return void
     */
    public function testNotInstantiable()
    {
        $class = new ReflectionClass(SingletonTestClass::class);

        static::assertFalse($class->isInstantiable());
    }

    /**
     * Assert that the class cannot be cloned.
     *
     * @return void
     */
    public function testNotCloneable()
    {
        $class = new ReflectionClass(SingletonTestClass::class);

        static::assertFalse($class->isCloneable());
    }

    /**
     * Test instance getter.
     *
     * @return void
     */
    public function testGetInstance()
    {
        $instance = SingletonTestClass::getInstance();

        $anotherInstance = SingletonTestClass::getInstance();

        static::assertSame($instance, $anotherInstance);
    }
}
