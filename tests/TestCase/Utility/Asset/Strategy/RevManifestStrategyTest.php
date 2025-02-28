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
namespace BEdita\WebTools\Test\TestCase\Utility\Asset\Strategy;

use BEdita\WebTools\Utility\Asset\Strategy\RevManifestStrategy;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * {@see \BEdita\WebTools\Utility\Asset\Strategy\RevManifestStrategy} Test Case
 */
#[CoversClass(RevManifestStrategy::class)]
#[CoversMethod(RevManifestStrategy::class, 'get')]
class RevManifestStrategyTest extends TestCase
{
    /**
     * Data provider for `testGet()`
     *
     * @return array
     */
    public static function getProvider(): array
    {
        return [
            'name' => [
                'support.xyz123.js',
                'support',
            ],
            'name with extension' => [
                'script-622a2cc4f5.js',
                'script.js',
            ],
            'name and extension' => [
                'script-622a2cc4f5.js',
                'script',
                'js',
            ],
            'not found' => [
                null,
                'gustavo',
            ],
        ];
    }

    /**
     * Test that get asset name works as expected.
     *
     * @param string $expected The expected path
     * @param string $name The name
     * @param string|null $extension The extension
     * @return void
     */
    #[DataProvider('getProvider')]
    public function testGet(?string $expected, string $name, ?string $extension = null): void
    {
        $strategy = new RevManifestStrategy();
        $actual = $strategy->get($name, $extension);
        static::assertEquals($expected, $actual);
    }
}
