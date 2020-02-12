<?php
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

/**
 * Utility class to handle asset names with revisions/signatures.
 *
 * Rev manifest file default path is `config/rev-manifest.json`
 * Other file paths may be used via `$config['manifestPath']`
 */
class AssetsRevisions
{
    /**
     * Array having asset names as keys and revved asset names as values
     *
     * @var array
     */
    static protected $assets = null;

    /**
     * Load manifest
     *
     * @param array $config
     * @return void
     */
    public static function loadManifest(string $path = null): void
    {
        static::$assets = [];
        if (empty($path)) {
            $path = CONFIG . 'rev-manifest.json';
        }
        if (file_exists($path)) {
            static::$assets = (array)json_decode(file_get_contents($path), true);
        }
    }

    /**
     * Retrieve `revved` asset name if found in manifest or return canonical asset name otherwise
     *
     * @param string $name Canonical asset name (un-revved)
     * @param string $extension Optional extension to use to search asset, like '.js' or '.css'
     * @return string
     */
    public static function get(string $name, string $extension = null): string
    {
        if (static::$assets === null) {
            static::loadManifest();
        }

        if (!empty(static::$assets[$name])) {
            return (string)static::$assets[$name];
        }
        if (!empty($extension) && !empty(static::$assets[$name . $extension])) {
            return (string)static::$assets[$name . $extension];
         }

        return $name;
    }

    /**
     * Retrieve `revved` asset names array via ::get() call
     *
     * @param array $names Canonical asset names (un-revved)
     * @param string $extension Optional extension to use to search asset, like '.js' or '.css'
     * @return array
     */
    public static function getMulti(array $names, string $extension = null): array
    {
        foreach ($names as $k => $val) {
            $names[$k] = static::get($val, $extension);
        }

        return $names;
    }
}
