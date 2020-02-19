<?php
declare(strict_types=1);

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

use BEdita\WebTools\Utility\AssetsRevisions;
use Cake\View\Helper;

/**
 * Asset Helper to handle asset names with signatures.
 *
 * @see AssetsRevisions for details
 */
class AssetHelper extends Helper
{
    /**
     * Retrieve `revved` asset name if found in manifest or return canonical asset name otherwise
     *
     * @param string $name Canonical asset name (un-revved)
     * @return string
     * @deprecated Deprecated since 1.3.0 Use `AssetsRevisions::get` or `Html` helper methods `script` or `css` directly.
     */
    public function get(string $name): string
    {
        return AssetsRevisions::get($name);
    }
}
