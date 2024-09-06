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

use Cake\TestSuite\TestCase;

/**
 * {@see \BEdita\WebTools\View\Twig\BeditaTwigExtension} Test Case
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
     */
    public function testGetName(): void
    {
        $twigExtension = new \BEdita\WebTools\View\Twig\BeditaTwigExtension();
        $this->assertEquals('bedita', $twigExtension->getName());
    }

    /**
     * Test `getFunctions` method
     *
     * @return void
     * @covers ::getFunctions()
     */
    public function testGetFunctions(): void
    {
        $twigExtension = new \BEdita\WebTools\View\Twig\BeditaTwigExtension();
        $functions = $twigExtension->getFunctions();
        $this->assertCount(2, $functions);
        $this->assertInstanceOf(\Twig\TwigFunction::class, $functions[0]);
        $this->assertEquals('config', $functions[0]->getName());
        $debug = \Cake\Core\Configure::read('debug');
        $this->assertEquals($debug, $functions[0]->getCallable()('debug'));
        $this->assertInstanceOf(\Twig\TwigFunction::class, $functions[1]);
        $this->assertEquals('write_config', $functions[1]->getName());
        $this->assertNull($functions[1]->getCallable()('debug', true));
        $this->assertEquals(true, $functions[0]->getCallable()('debug'));
        \Cake\Core\Configure::write('debug', $debug);
    }

    /**
     * Test `getFilters` method
     *
     * @return void
     * @covers ::getFilters()
     */
    public function testGetFilters(): void
    {
        $twigExtension = new \BEdita\WebTools\View\Twig\BeditaTwigExtension();
        $filters = $twigExtension->getFilters();
        $this->assertCount(3, $filters);
        $this->assertInstanceOf(\Twig\TwigFilter::class, $filters[0]);
        $this->assertEquals('shuffle', $filters[0]->getName());
        $actual = $filters[0]->getCallable()([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
        $this->assertCount(10, $actual);
        $this->assertNotEquals([1, 2, 3, 4, 5, 6, 7, 8, 9, 10], $actual);
        $this->assertInstanceOf(\Twig\TwigFilter::class, $filters[1]);
        $this->assertEquals('ksort', $filters[1]->getName());
        $actual = $filters[1]->getCallable()(['c' => 2, 'a' => 0, 'b' => 1]);
        $expected = ['a' => 0, 'b' => 1, 'c' => 2];
        $this->assertEquals($expected, $actual);
        $this->assertInstanceOf(\Twig\TwigFilter::class, $filters[2]);
        $this->assertEquals('krsort', $filters[2]->getName());
        $actual = $filters[2]->getCallable()(['c' => 2, 'a' => 0, 'b' => 1]);
        $expected = ['c' => 2, 'b' => 1, 'a' => 0];
        $this->assertEquals($expected, $actual);
    }
}
