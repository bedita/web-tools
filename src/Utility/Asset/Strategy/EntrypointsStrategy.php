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
 * Entrypoints asset strategy.
 * This strategy is based on map produced by Webpack Encore and expects a JSON assets map like
 *
 * ```
 * {
 *     "entrypoints": {
 *         "app": {
 *             "js": [
 *                 "/build/runtime.f011bcb1.js",
 *                 "/build/0.54651780.js",
 *                 "/build/app.82269f26.js"
 *             ]
 *         },
 *         "style": {
 *             "js": [
 *                 "/build/runtime.f011bcb1.js"
 *             ],
 *             "css": [
 *                 "/build/style.12c5249c.css"
 *             ]
 *         }
 *     }
 * }
 * ```
 *
 * @see https://symfony.com/doc/current/frontend.html
 */
class EntrypointsStrategy extends AssetStrategy
{
    /**
     * {@inheritDoc}
     */
    protected $_defaultConfig = [
        'manifestPath' => WWW_ROOT . 'build' . DS . 'entrypoints.json',
    ];

    /**
     * {@inheritDoc}
     */
    public function get(string $name, ?string $extension = null)
    {
        $path = sprintf('entrypoints.%s', $name);
        if (!empty($extension)) {
            $path .= sprintf('.%s', $extension);
        }

        return Hash::get($this->assets, $path);
    }
}
