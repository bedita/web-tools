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
namespace BEdita\WebTools\Test\TestCase\Utility;

use BEdita\WebTools\Utility\Asset\Strategy\EntrypointsStrategy;
use BEdita\WebTools\Utility\Asset\Strategy\RevManifestStrategy;
use BEdita\WebTools\Utility\AssetsRevisions;
use Cake\TestSuite\TestCase;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * {@see \BEdita\WebTools\Utility\AssetsRevisions} Test Case
 */
#[CoversClass(AssetsRevisions::class)]
class AssetsRevisionsTest extends TestCase
{
    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        AssetsRevisions::setStrategy(new RevManifestStrategy());
    }

    /**
     * @inheritDoc
     */
    public function tearDown(): void
    {
        AssetsRevisions::clearStrategy();
    }

    /**
     * Test set, get and reset strategy
     *
     * @return void
     */
    #[CoversMethod(AssetsRevisions::class, 'setStrategy')]
    #[CoversMethod(AssetsRevisions::class, 'getStrategy')]
    #[CoversMethod(AssetsRevisions::class, 'clearStrategy')]
    public function testStrategy(): void
    {
        AssetsRevisions::setStrategy(new EntrypointsStrategy());
        static::assertInstanceOf(EntrypointsStrategy::class, AssetsRevisions::getStrategy());

        AssetsRevisions::clearStrategy();
        static::assertNull(AssetsRevisions::getStrategy());
    }

    /**
     * Data provider for `testGet` test case.
     *
     * @return array
     */
    public static function getProvider(): array
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
                'css',
            ],
            'extension missing' => [
                'script',
                'script',
                'css',
            ],
        ];
    }

    /**
     * Test `get` method.
     *
     * @param string $expected The expected result
     * @param string $name The asset name
     * @param string $extension The asset extension
     * @return void
     */
    #[CoversMethod(AssetsRevisions::class, 'get')]
    #[DataProvider('getProvider')]
    public function testGet(string $expected, string $name, ?string $extension = null): void
    {
        $result = AssetsRevisions::get($name, $extension);
        static::assertEquals($expected, $result);
    }

    /**
     * Test that `get()` method returns the passed asset name when no strategy was set.
     *
     * @return void
     */
    #[CoversMethod(AssetsRevisions::class, 'get')]
    public function testGetWithoutStrategy(): void
    {
        AssetsRevisions::clearStrategy();
        $name = 'app';
        static::assertEquals($name, AssetsRevisions::get($name));
    }

    /**
     * Test `getMulti` method
     *
     * @return void
     */
    #[CoversMethod(AssetsRevisions::class, 'getMulti')]
    public function testGetMulti(): void
    {
        $expected = [
            'script-622a2cc4f5.js',
            'about',
        ];

        $result = AssetsRevisions::getMulti(['script', 'about'], 'js');
        static::assertEquals($expected, $result);
    }

    /**
     * Test `loadManifest`
     *
     * @return void
     */
    #[CoversMethod(AssetsRevisions::class, 'loadManifest')]
    public function testLoadManifest(): void
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

    /**
     * Test that an exception is rised trying to load manifest
     * without an asset strategy set.
     *
     * @return void
     * @expectException \LogicException
     */
    #[CoversMethod(AssetsRevisions::class, 'loadManifest')]
    public function testLoadManifestWithoutStrategy(): void
    {
        $this->expectException(LogicException::class);

        AssetsRevisions::clearStrategy();
        AssetsRevisions::loadManifest();
    }
}
