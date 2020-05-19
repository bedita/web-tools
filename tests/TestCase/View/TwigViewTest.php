<?php
declare(strict_types=1);

/**
 * BEdita, API-first content management framework
 * Copyright 2018 ChannelWeb Srl, Chialab Srl
 *
 * This file is part of BEdita: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * See LICENSE.LGPL or <http://gnu.org/licenses/lgpl-3.0.html> for more details.
 */
namespace BEdita\WebTools\Test\TestCase\View;

use BEdita\WebTools\View\TwigView;
use Cake\TestSuite\TestCase;

/**
 * {@see \BEdita\WebTools\View\AppView} Test Case
 *
 * @coversDefaultClass \BEdita\WebTools\View\TwigView
 */
class TwigViewTest extends TestCase
{
    /**
     * Test `initialize` method
     *
     * @return void
     * @covers ::initialize()
     */
    public function testInitialize(): void
    {
        $View = new TwigView();
        $extensions = $View->getTwig()->getExtensions();
        static::assertNotEmpty($extensions);
        static::assertArrayHasKey('BEdita\WebTools\View\Twig\BeditaTwigExtension', $extensions);
        static::assertFalse($View->getConfig('environment.strict_variables'));
    }
}
