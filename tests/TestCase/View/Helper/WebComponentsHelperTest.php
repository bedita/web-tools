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
namespace BEdita\WebTools\Test\TestCase\View\Helper;

use BEdita\WebTools\View\Helper\WebComponentsHelper;
use Cake\TestSuite\TestCase;
use Cake\View\View;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * {@see \BEdita\WebTools\View\Helper\WebComponentsHelper} Test Case
 *
 * @coversDefaultClass \BEdita\WebTools\View\Helper\WebComponentsHelper
 */
class WebComponentsHelperTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \BEdita\WebTools\View\Helper\WebComponentsHelper
     */
    public $WebComponents;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        parent::setUp();

        // create helper
        $this->WebComponents = new WebComponentsHelper(new View());
    }

    /**
     * @inheritDoc
     */
    public function tearDown(): void
    {
        unset($this->WebComponents);

        parent::tearDown();
    }

    /**
     * Data provider for `testProps` test case.
     *
     * @return array
     */
    public function propsProvider(): array
    {
        return [
            'empty' => [
                [],
                [],
            ],
            'string' => [
                [
                    'data-wc' => '0',
                    'id' => 'test',
                ],
                ['id' => 'test'],
            ],
            'numeric' => [
                [
                    'data-wc' => '0',
                    'value' => '2',
                ],
                ['value' => '2'],
            ],
            'array' => [
                [
                    'data-wc' => '0',
                ],
                ['data' => [1, 2, 3]],
            ],
            'mixed' => [
                [
                    'data-wc' => '0',
                    'id' => 'test',
                    'value' => '2',
                ],
                ['id' => 'test', 'value' => '2', 'data' => [1, 2, 3]],
            ],
        ];
    }

    /**
     * Test `props` method
     *
     * @covers ::props()
     * @param array $expected The expected result
     * @param array $properties The element properties
     * @return void
     */
    #[DataProvider('propsProvider')]
    public function testProps($expected, $properties): void
    {
        $result = $this->WebComponents->props($properties);
        static::assertEquals($expected, $result);
    }

    /**
     * Data provider for `testIs` test case.
     *
     * @return array
     */
    public function isProvider(): array
    {
        return [
            'simple' => [
                'is="bedita-table"',
                ['bedita-table', []],
            ],
            'string' => [
                'is="bedita-table" data-wc="0" id="test"',
                ['bedita-table', [ 'id' => 'test' ]],
            ],
            'numeric' => [
                'is="bedita-input" data-wc="0" value="2"',
                ['bedita-input', [ 'value' => '2' ]],
            ],
            'array' => [
                'is="bedita-input" data-wc="0"',
                ['bedita-input', [ 'data' => [1, 2, 3] ]],
            ],
        ];
    }

    /**
     * Test `is` method
     *
     * @covers ::is()
     * @param string $expected The expected result
     * @param array $properties The element properties
     * @return void
     */
    #[DataProvider('isProvider')]
    public function testIs($expected, $properties): void
    {
        $result = $this->WebComponents->is($properties[0], $properties[1]);
        static::assertEquals($expected, $result);
    }

    /**
     * Test `is` method with script path
     *
     * @return void
     * @covers ::is()
     */
    public function testIsWithScript(): void
    {
        $actual = $this->WebComponents->is('bedita-input', ['value' => '2'], 'path/to/script.js');
        $expected = 'is="bedita-input" data-wc="0" value="2"';
        static::assertEquals($expected, $actual);
    }

    /**
     * Data provider for `testElement` test case.
     *
     * @return array
     */
    public function elementProvider(): array
    {
        return [
            'simple' => [
                '<bedita-table></bedita-table>',
                ['bedita-table', []],
            ],
            'string' => [
                '<bedita-table data-wc="0" id="test"></bedita-table>',
                ['bedita-table', [ 'id' => 'test' ]],
            ],
            'numeric' => [
                '<bedita-input data-wc="0" value="2"></bedita-input>',
                ['bedita-input', [ 'value' => '2' ]],
            ],
            'array' => [
                '<bedita-input data-wc="0"></bedita-input>',
                ['bedita-input', [ 'data' => [1, 2, 3] ]],
            ],
        ];
    }

    /**
     * Test `element` method
     *
     * @covers ::element()
     * @param string $expected The expected result
     * @param array $properties The element properties
     * @return void
     */
    #[DataProvider('elementProvider')]
    public function testElement($expected, $properties): void
    {
        $result = $this->WebComponents->element($properties[0], $properties[1]);
        static::assertEquals($expected, $result);
    }

    /**
     * Test `element` method with script path
     *
     * @return void
     * @covers ::element()
     */
    public function testElementWithScript(): void
    {
        $actual = $this->WebComponents->element('bedita-input', ['value' => '2'], 'path/to/script.js');
        $expected = '<bedita-input data-wc="0" value="2"></bedita-input>';
        static::assertEquals($expected, $actual);
    }
}
