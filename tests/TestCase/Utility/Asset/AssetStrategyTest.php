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

use BEdita\WebTools\Utility\Asset\AssetStrategy;
use Cake\TestSuite\TestCase;

/**
 * {@see \BEdita\WebTools\Utility\Asset\AssetStrategy} Test Case
 *
 * @coversDefaultClass \BEdita\WebTools\Utility\Asset\AssetStrategy
 */
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
            public function get(string $name, ?string $extension = null)
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
    public function manifestPathProvider(): array
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
     * @dataProvider manifestPathProvider()
     * @covers ::__construct()
     */
    public function testManifestPath(string $expected, array $config): void
    {
        $strategy = $this->getInstance($config);

        static::assertEquals($expected, $strategy->getConfig('manifestPath'));
    }

    /**
     * Test `loadASsets()`
     *
     * @return void
     * @covers ::loadAssets()
     */
    public function testLoadAssets(): void
    {
        $path = WWW_ROOT . 'custom-manifest.json';
        $expected = json_decode(file_get_contents($path), true);
        $strategy = $this->getInstance(['manifestPath' => $path]);
        static::assertEquals($expected, $strategy->get('all'));
    }
}
