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
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * {@see \BEdita\WebTools\View\TwigView} Test Case
 */
#[CoversClass(TwigView::class)]
class TwigViewTest extends TestCase
{
    /**
     * Test `initialize` method
     *
     * @return void
     */
    public function testInitialize(): void
    {
        $view = new TwigView();
        $extensions = $view->getTwig()->getExtensions();
        static::assertNotEmpty($extensions);
        static::assertArrayHasKey('BEdita\WebTools\View\Twig\BeditaTwigExtension', $extensions);
        static::assertFalse($view->getConfig('environment.strict_variables'));
    }
}
