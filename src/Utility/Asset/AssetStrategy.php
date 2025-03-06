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
namespace BEdita\WebTools\Utility\Asset;

use Cake\Core\InstanceConfigTrait;

/**
 * Abstract base class for asset strategies.
 * Every asset strategy should extend this class or implements `AssetStrategyInterface`
 */
abstract class AssetStrategy implements AssetStrategyInterface
{
    use InstanceConfigTrait;

    /**
     * Default configuration.
     *
     * - `manifestPath` is the file path used as manifest for assets
     *
     * @var array
     */
    protected array $_defaultConfig = [
        'manifestPath' => '',
    ];

    /**
     * The assets map loaded.
     *
     * @var array
     */
    protected array $assets = [];

    /**
     * Initialize an asset strategy instance. Called after the constructor.
     *
     * - write conf
     * - load assets
     *
     * @param array $config The configuration for the asset strategy
     */
    public function __construct(array $config = [])
    {
        $this->setConfig($config);
        $this->loadAssets();
    }

    /**
     * Load assets map.
     * If no map path is passed then it uses the configured one.
     *
     * @param string|null $manifestPath The optional file path to use
     * @return void
     */
    public function loadAssets(?string $manifestPath = null): void
    {
        $this->assets = [];
        if (empty($manifestPath)) {
            $manifestPath = $this->getConfig('manifestPath');
        }
        if (file_exists($manifestPath)) {
            $this->assets = (array)json_decode(file_get_contents($manifestPath), true);
        }
    }
}
