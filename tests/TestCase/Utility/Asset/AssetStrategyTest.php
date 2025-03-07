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
namespace BEdita\WebTools\Test\TestCase\Utility\Asset;

use BEdita\WebTools\Utility\Asset\AssetStrategy;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * {@see \BEdita\WebTools\Utility\Asset\AssetStrategy} Test Case
 */
#[CoversClass(AssetStrategy::class)]
#[CoversMethod(AssetStrategy::class, '__construct')]
#[CoversMethod(AssetStrategy::class, 'loadAssets')]
class AssetStrategyTest extends TestCase
{
    /**
     * Return an instance of anonymous class that extends AssetStrategy
     *
     * @param array $config The configuration to use
     * @return AssetStrategy
     */
    protected function getInstance(array $config = []): AssetStrategy
    {
        return new class ($config) extends AssetStrategy {
            public function get(string $name, ?string $extension = null): string|array|null
            {
                return $this->assets;
            }
        };
    }

    /**
     * Data provider for `testManifestPath()`
     *
     * @return array
     */
    public static function manifestPathProvider(): array
    {
        return [
            'default' => [
                '',
                [],
            ],
            'custom' => [
                '/manifest/custom/path.json',
                [
                    'manifestPath' => '/manifest/custom/path.json',
                ],
            ],
        ];
    }

    /**
     * Test that manifest path is well configured.
     *
     * @param string $expected The expected path
     * @param array $config The configuration used
     * @return void
     */
    #[DataProvider('manifestPathProvider')]
    public function testManifestPath(string $expected, array $config): void
    {
        $strategy = $this->getInstance($config);

        static::assertEquals($expected, $strategy->getConfig('manifestPath'));
    }

    /**
     * Test `loadASsets()`
     *
     * @return void
     */
    public function testLoadAssets(): void
    {
        $path = WWW_ROOT . 'custom-manifest.json';
        $expected = json_decode(file_get_contents($path), true);
        $strategy = $this->getInstance(['manifestPath' => $path]);
        static::assertEquals($expected, $strategy->get('all'));
    }
}
