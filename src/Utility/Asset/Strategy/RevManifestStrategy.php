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
namespace BEdita\WebTools\Utility\Asset\Strategy;

use BEdita\WebTools\Utility\Asset\AssetStrategy;
use Cake\Utility\Hash;

/**
 * RevManifest asset strategy.
 * This strategy expects a JSON assets map like
 *
 * ```
 * {
 *     "app.js": "app.13gdr5.js",
 *     "style.css: "style.lgcf6a.js"
 * }
 * ```
 */
class RevManifestStrategy extends AssetStrategy
{
    /**
     * @inheritDoc
     */
    protected array $_defaultConfig = [
        'manifestPath' => WWW_ROOT . 'rev-manifest.json',
    ];

    /**
     * @inheritDoc
     */
    public function get(string $name, ?string $extension = null): array|string
    {
        if (!empty($extension)) {
            $name .= sprintf('.%s', $extension);
        }
        $val = Hash::get($this->assets, $name);

        return is_string($val) ? $val : (array)$val;

    }
}
