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
namespace BEdita\WebTools\Test\TestCase\View\Helper;

use BEdita\WebTools\View\Helper\WebComponentsHelper;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;
use Cake\View\View;

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
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        // create helper
        $this->WebComponents = new WebComponentsHelper(new View());
    }

    /**
     * {@inheritDoc}
     */
    public function tearDown(): void
    {
        unset($this->WebComponents);

        parent::tearDown();
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
                ['bedita-table', [ 'id' => 'test' ]]
            ],
            'numeric' => [
                'is="bedita-input" data-wc="0" value="2"',
                ['bedita-input', [ 'value' => '2' ]]
            ],
            'array' => [
                'is="bedita-input" data-wc="0"',
                ['bedita-input', [ 'data' => [1, 2, 3] ]]
            ],
        ];
    }

    /**
     * Test `is` method
     *
     * @dataProvider isProvider()
     * @covers ::is()
     *
     * @param string|string[] $expected The expected result
     * @param array $properties The element properties
     * @return void
     */
    public function testIs($expected, $properties): void
    {
        $result = $this->WebComponents->is($properties[0], $properties[1]);
        static::assertEquals($expected, $result);
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
                '<bedita-table ></bedita-table>',
                ['bedita-table', []],
            ],
            'string' => [
                '<bedita-table data-wc="0" id="test"></bedita-table>',
                ['bedita-table', [ 'id' => 'test' ]]
            ],
            'numeric' => [
                '<bedita-input data-wc="0" value="2"></bedita-input>',
                ['bedita-input', [ 'value' => '2' ]]
            ],
            'array' => [
                '<bedita-input data-wc="0"></bedita-input>',
                ['bedita-input', [ 'data' => [1, 2, 3] ]]
            ],
        ];
    }

    /**
     * Test `element` method
     *
     * @dataProvider elementProvider()
     * @covers ::element()
     *
     * @param string|string[] $expected The expected result
     * @param array $properties The element properties
     * @return void
     */
    public function testElement($expected, $properties): void
    {
        $result = $this->WebComponents->element($properties[0], $properties[1]);
        static::assertEquals($expected, $result);
    }
}
