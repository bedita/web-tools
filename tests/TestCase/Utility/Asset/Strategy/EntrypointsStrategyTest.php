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

use BEdita\WebTools\Utility\Asset\Strategy\EntrypointsStrategy;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * {@see \BEdita\WebTools\Utility\Asset\Strategy\EntrypointsStrategy} Test Case
 *
 * @coversDefaultClass \BEdita\WebTools\Utility\Asset\Strategy\EntrypointsStrategy
 */
class EntrypointsStrategyTest extends TestCase
{
    /**
     * Data provider for `testGet()`
     *
     * @return array
     */
    public static function getProvider(): array
    {
        return [
            'not found' => [
                null,
                'gustavo',
            ],
            'all assets for entrypoint' => [
                [
                    'js' => [
                        '/build/runtime.f011bcb1.js',
                    ],
                    'css' => [
                        '/build/style.12c5249c.css',
                    ],
                ],
                'style',
            ],
            'all js assets for entrypoint' => [
                [
                    '/build/runtime.f011bcb1.js',
                    '/build/0.54651780.js',
                    '/build/app.82269f26.js',
                ],
                'app',
                'js',
            ],
        ];
    }

    /**
     * Test that get asset name works as expected.
     *
     * @param string $expected The expected path
     * @param array $name The configuration used
     * @return void
     * @covers ::get()
     */
    #[DataProvider('getProvider')]
    public function testGet(?array $expected, string $name, ?string $extension = null): void
    {
        $strategy = new EntrypointsStrategy(['manifestPath' => WWW_ROOT . 'entrypoints.json']);

        static::assertEquals($expected, $strategy->get($name, $extension));
    }
}
