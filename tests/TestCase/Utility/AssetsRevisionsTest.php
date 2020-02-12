<?php
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

use BEdita\WebTools\Utility\AssetsRevisions;
use Cake\TestSuite\TestCase;

/**
 * {@see \BEdita\WebTools\Utility\AssetsRevisions} Test Case
 *
 * @coversDefaultClass \BEdita\WebTools\Utility\AssetsRevisions
 */
class AssetsRevisionsTest extends TestCase
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
                'script-622a2cc4f5.js',
                'script.js',
            ],
            'not found' => [
                'functions.js',
                'functions.js',
            ],
            'extension' => [
                'style-b7c54b4c5a.css',
                'style',
                '.css',
            ],
            'extension missing' => [
                'script',
                'script',
                '.css'
            ],
        ];
    }

    /**
     * Test `get` method
     *
     * @dataProvider getProvider()
     * @covers ::get()
     *
     * @param string $expected The expected result
     * @param string $name The asset name
     * @param string $extension The asset extension
     * @return void
     */
    public function testGet(string $expected, string $name, string $extension = null): void
    {
        $result = AssetsRevisions::get($name, $extension);
        static::assertEquals($expected, $result);
    }

    /**
     * Test `getMulti` method
     *
     * @covers ::getMulti()
     * @return void
     */
    public function testGetMulti(): void
    {
        $expected = [
            'script-622a2cc4f5.js',
            'about',
        ];

        $result = AssetsRevisions::getMulti(['script', 'about'], '.js');
        static::assertEquals($expected, $result);
    }

    /**
     * Test `loadManifest`
     *
     * @covers ::loadManifest()
     * @return void
     */
    public function testLoadManifest()
    {
        // use different path
        $path = '/some/path/manifest.json';
        AssetsRevisions::loadManifest($path);

        $result = AssetsRevisions::get('script.js');
        static::assertEquals('script.js', $result);

        // reload default
        AssetsRevisions::loadManifest();
        $result = AssetsRevisions::get('script.js');
        static::assertEquals('script-622a2cc4f5.js', $result);
    }
}
