<?php
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
namespace BEdita\WebTools\Test\TestCase\View\Twig;

use BEdita\WebTools\View\Twig\BeditaTwigExtension;
use Cake\TestSuite\TestCase;

/**
 * {@see BEdita\WebTools\View\Twig\BeditaTwigExtension} Test Case
 *
 * @coversDefaultClass \BEdita\WebTools\View\Twig\BeditaTwigExtension
 */
class BeditaTwigExtensionTest extends TestCase
{
    /**
     * Test `getName` method
     *
     * @return void
     * @covers ::getName()
     * @covers ::getFunctions()
     */
    public function testExtension(): void
    {
        $extension = new BeditaTwigExtension();
        $expected = 'bedita';
        $actual = $extension->getName();
        static::assertSame($expected, $actual);

        $expected = 3;
        $actual = count($extension->getFunctions());
        static::assertSame($expected, $actual);
    }
}
