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

/**
 * Interface that describe an asset strategy.
 */
interface AssetStrategyInterface
{
    /**
     * Retrieve the asset corresponding to the name passed.
     *
     * @param string $name The name used to looking for the asset
     * @param string|null $extension Optional asset extension as 'js' or 'css'
     * @return array|string
     */
    public function get(string $name, ?string $extension = null): string|array;

    /**
     * Load assets map optionally using a file path.
     *
     * @param string|null $path The optional file path
     * @return void
     */
    public function loadAssets(?string $path = null): void;
}
