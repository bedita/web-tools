<?php
declare(strict_types=1);

/**
 * BEdita, API-first content management framework
 * Copyright 2024 ChannelWeb Srl, Chialab Srl
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
use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * {@see \BEdita\WebTools\View\Twig\BeditaTwigExtension} Test Case
 */
#[CoversClass(BeditaTwigExtension::class)]
class BeditaTwigExtensionTest extends TestCase
{
    /**
     * Test `getName` method
     *
     * @return void
     */
    #[CoversMethod(BeditaTwigExtension::class, 'getName')]
    public function testGetName(): void
    {
        $twigExtension = new BeditaTwigExtension();
        $this->assertEquals('bedita', $twigExtension->getName());
    }

    /**
     * Test `getFunctions` method
     *
     * @return void
     */
    #[CoversMethod(BeditaTwigExtension::class, 'getFunctions')]
    public function testGetFunctions(): void
    {
        $twigExtension = new BeditaTwigExtension();
        $functions = $twigExtension->getFunctions();
        $this->assertCount(2, $functions);
        $this->assertInstanceOf(TwigFunction::class, $functions[0]);
        $this->assertEquals('config', $functions[0]->getName());
        $debug = Configure::read('debug');
        $this->assertEquals($debug, $functions[0]->getCallable()('debug'));
        $this->assertInstanceOf(TwigFunction::class, $functions[1]);
        $this->assertEquals('write_config', $functions[1]->getName());
        $this->assertNull($functions[1]->getCallable()('debug', true));
        $this->assertEquals(true, $functions[0]->getCallable()('debug'));
        Configure::write('debug', $debug);
    }

    /**
     * Test `getFilters` method
     *
     * @return void
     */
    #[CoversMethod(BeditaTwigExtension::class, 'getFilters')]
    public function testGetFilters(): void
    {
        $twigExtension = new BeditaTwigExtension();
        $filters = $twigExtension->getFilters();
        $this->assertCount(3, $filters);
        $this->assertInstanceOf(TwigFilter::class, $filters[0]);
        $this->assertEquals('shuffle', $filters[0]->getName());
        $actual = $filters[0]->getCallable()([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
        $this->assertCount(10, $actual);
        $this->assertNotEquals([1, 2, 3, 4, 5, 6, 7, 8, 9, 10], $actual);
        $this->assertInstanceOf(TwigFilter::class, $filters[1]);
        $this->assertEquals('ksort', $filters[1]->getName());
        $actual = $filters[1]->getCallable()(['c' => 2, 'a' => 0, 'b' => 1]);
        $expected = ['a' => 0, 'b' => 1, 'c' => 2];
        $this->assertEquals($expected, $actual);
        $this->assertInstanceOf(TwigFilter::class, $filters[2]);
        $this->assertEquals('krsort', $filters[2]->getName());
        $actual = $filters[2]->getCallable()(['c' => 2, 'a' => 0, 'b' => 1]);
        $expected = ['c' => 2, 'b' => 1, 'a' => 0];
        $this->assertEquals($expected, $actual);
    }
}
