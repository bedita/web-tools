<?php
declare(strict_types=1);

/**
 * BEdita, API-first content management framework
 * Copyright 2019 ChannelWeb Srl, Chialab Srl
 *
 * This file is part of BEdita: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * See LICENSE.LGPL or <http://gnu.org/licenses/lgpl-3.0.html> for more details.
 */
namespace BEdita\WebTools\Test\TestCase\View\Helper;

use BEdita\WebTools\View\Helper\AssetHelper;
use Cake\TestSuite\TestCase;
use Cake\View\View;

/**
 * {@see \BEdita\WebTools\View\Helper\AssetHelper} Test Case
 *
 * @coversDefaultClass \BEdita\WebTools\View\Helper\AssetHelper
 */
class AssetHelperTest extends TestCase
{
    /**
     * Data provider for `testGet` test case.
     *
     * @return array
     */
    public function getProvider(): array
    {
        return [
            'simple' => [
                'script-h74fba9b9e.js',
                'script.js',
            ],
            'not found' => [
                'functions.js',
                'functions.js',
            ],
            'custom path' => [
                'script.js',
                'script.js',
                [
                    'manifestPath' => ROOT . 'mymanifestpath',
                ],
            ],
        ];
    }

    /**
     * Test `get` method
     *
     * @dataProvider getProvider()
     * @covers ::get()
     * @covers ::initialize()
     *
     * @param string $expected The expected result
     * @param string $name The asset name
     * @param string $config The helper config
     * @return void
     */
    public function testGet(string $expected, string $name, array $config = []): void
    {
        $Asset = new AssetHelper(new View(), $config);
        $result = $Asset->get($name);
        static::assertEquals($expected, $result);
    }
}
