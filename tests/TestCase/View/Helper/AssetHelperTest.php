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
namespace BEdita\WebTools\Test\TestCase\View\Helper;

use BEdita\WebTools\Utility\AssetsRevisions;
use BEdita\WebTools\View\Helper\AssetHelper;
use Cake\TestSuite\TestCase;
use Cake\View\View;

/**
 * {@see \BEdita\WebTools\View\Helper\AssetHelper} Test Case
 *
 * @coversDefaultClass \BEdita\WebTools\View\Helper\AssetHelper
 */
class AssetHelperTest extends TestCase
{
    /**
     * Test `get` method
     *
     * @covers ::get()
     *
     * @return void
     */
    public function testGet(): void
    {
        $Asset = new AssetHelper(new View());
        $result = $Asset->get('script.js');
        static::assertEquals('script-622a2cc4f5.js', $result);
    }
}
