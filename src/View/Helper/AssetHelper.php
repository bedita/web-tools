<?php
/**
 * BEdita, API-first content management framework
 * Copyright 2019 ChannelWeb Srl, Chialab Srl
 *
 * This file is part of BEdita: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * See LICENSE.LGPL or <http://gnu.org/licenses/lgpl-3.0.html> for more details.
 */

namespace BEdita\WebTools\View\Helper;

use Cake\Utility\Hash;
use Cake\View\Helper;

/**
 * Asset helper
 */
class AssetHelper extends Helper
{
    /**
     * {@inheritDoc}
     */
    public $helpers = ['Html'];

    /**
     * Array having asset names as keys and revved asset names as values
     *
     * @var array
     */
    protected $assets = [];

    /**
     * {@inheritDoc}
     */
    public function initialize(array $config): void
    {
        $manifestPath = Hash::get($config, 'manifestPath', CONFIG . 'rev-manifest.json');
        if (!file_exists($manifestPath)) {
            return;
        }

        $this->assets = json_decode(file_get_contents($manifestPath), true);
    }

    /**
     * Retrieve revved asset name if found in manifest or return canonical asset name otherwise
     *
     * @param string $name Canonical asset name (un-revved)
     * @return string
     */
    public function get(string $name): string
    {
        if (!empty($this->assets[$name])) {
            $name = (string)$this->assets[$name];
        }

        return $name;
    }
}
