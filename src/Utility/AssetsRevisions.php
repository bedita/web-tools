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
namespace BEdita\WebTools\Utility;

use BEdita\WebTools\Utility\Asset\AssetStrategyInterface;
use LogicException;

/**
 * Utility class to handle asset names with revisions/signatures.
 *
 * It can use different strategies to search assets.
 */
class AssetsRevisions
{
    /**
     * The asset strategy adopted.
     *
     * @var \BEdita\WebTools\Utility\Asset\AssetStrategyInterface|null
     */
    protected static ?AssetStrategyInterface $strategy = null;

    /**
     * Set an asset strategy to be used.
     *
     * @param \BEdita\WebTools\Utility\Asset\AssetStrategyInterface $strategy The asset strategy to use
     * @return void
     */
    public static function setStrategy(AssetStrategyInterface $strategy): void
    {
        static::$strategy = $strategy;
    }

    /**
     * Get the current asset strategy adopted.
     *
     * @return \BEdita\WebTools\Utility\Asset\AssetStrategyInterface|null
     */
    public static function getStrategy(): ?AssetStrategyInterface
    {
        return static::$strategy;
    }

    /**
     * Clear asset strategy.
     *
     * @return void
     */
    public static function clearStrategy(): void
    {
        static::$strategy = null;
    }

    /**
     * Retrieve asset name or an array of assets .
     * Return canonical asset name if no assets was found.
     *
     * @param string $name Canonical asset name
     * @param string $extension Optional extension to use to search asset, like 'js' or 'css'
     * @return array|string
     */
    public static function get(string $name, ?string $extension = null): string|array
    {
        $strategy = static::getStrategy();
        if ($strategy === null) {
            return $name;
        }

        $asset = $strategy->get($name, $extension);
        if (!empty($asset)) {
            return $asset;
        }

        return $name;
    }

    /**
     * Retrieve asset names array via ::get() call
     *
     * @param array $names Canonical asset names
     * @param string $extension Optional extension to use to search asset, like 'js' or 'css'
     * @return array
     */
    public static function getMulti(array $names, ?string $extension = null): array
    {
        $assets = [];
        foreach ($names as $val) {
            $assets = array_merge($assets, (array)static::get($val, $extension));
        }

        return $assets;
    }

    /**
     * Load assets for adopted asset strategy.
     *
     * @param string $path Manifest file path
     * @return void
     * @throws \LogicException If startegy is not defined
     */
    public static function loadManifest(?string $path = null): void
    {
        $strategy = static::getStrategy();
        if ($strategy === null) {
            throw new LogicException('Missing asset strategy');
        }

        $strategy->loadAssets($path);
    }
}
