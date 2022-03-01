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
use BEdita\WebTools\View\TwigView;
use Cake\TestSuite\TestCase;

/**
 * {@see BEdita\WebTools\View\Twig\BeditaTwigExtension} Test Case
 *
 * @coversDefaultClass \BEdita\WebTools\View\Twig\BeditaTwigExtension
 */
class BeditaTwigExtensionTest extends TestCase
{
    public function setUp(): void
    {
        $this->extension = new BeditaTwigExtension();
        parent::setUp();
    }

    /**
     * Test getName, getFunctions, getFilters
     *
     * @return void
     * @covers ::getName()
     * @covers ::getFunctions()
     * @covers ::getFilters()
     */
    public function testExtension(): void
    {
        $expected = 'bedita';
        $actual = $this->extension->getName();
        static::assertSame($expected, $actual);

        $expected = 3;
        $actual = count($this->extension->getFunctions());
        static::assertSame($expected, $actual);

        $expected = 3;
        $actual = count($this->extension->getFilters());
        static::assertSame($expected, $actual);

        $expected = 'something';
        $callable = $this->getFunction('write_config')->getCallable();
        call_user_func($callable, 'foo', $expected);

        $callable = $this->getFunction('config')->getCallable();
        $actual = call_user_func($callable, 'foo');
        static::assertSame($expected, $actual);

        $this->expectException('Cake\View\Exception\MissingElementException');
        $context['_view'] = new TwigView();
        $callable = $this->getFunction('element')->getCallable();
        $actual = call_user_func($callable, $context, 'foo', []);
        static::assertNull($actual);
    }

    /**
     * Test filters
     *
     * @return void
     * @covers ::getFilters()
     */
    public function testFilters(): void
    {
        $callable = $this->getFilter('shuffle')->getCallable();
        $actual = call_user_func_array($callable, [[]]);
        static::assertEmpty($actual);

        $callable = $this->getFilter('ksort')->getCallable();
        $actual = call_user_func_array($callable, [['z' => 3, 'q' => 2, 'a' => 1]]);
        static::assertSame(['a' => 1, 'q' => 2, 'z' => 3], $actual);

        $callable = $this->getFilter('krsort')->getCallable();
        $actual = call_user_func_array($callable, [['a' => 1, 'q' => 2, 'z' => 3]]);
        static::assertSame(['z' => 3, 'q' => 2, 'a' => 1], $actual);
    }

    protected function getFilter($name)
    {
        $filters = $this->extension->getFilters();
        foreach ($filters as $filter) {
            if ($filter->getName() === $name) {
                return $filter;
            }
        }
    }

    protected function getFunction($name)
    {
        $functions = $this->extension->getFunctions();
        foreach ($functions as $function) {
            if ($function->getName() === $name) {
                return $function;
            }
        }
    }
}
