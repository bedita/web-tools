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
namespace BEdita\WebTools\Test\TestCase;

use BEdita\WebTools\Command\CacheClearallCommand;
use Cake\Console\CommandCollection;
use Cake\TestSuite\TestCase;
use TestApp\Application;

/**
 * {@see BEdita\WebTools\Plugin} Test Case
 *
 * @coversDefaultClass \BEdita\WebTools\Plugin
 */
class PluginTest extends TestCase
{
    /**
     * Test `console` method
     *
     * @return void
     *
     * @covers ::console
     */
    public function testConsole(): void
    {
        $app = new Application(CONFIG);
        $app->bootstrap();
        $commands = $app->console(new CommandCollection([]));
        $commands = $app->pluginConsole($commands);
        $cacheClearAll = $commands->get('cache clear_all');
        static::assertNotEmpty($cacheClearAll);
        static::assertEquals(CacheClearallCommand::class, $cacheClearAll);
    }
}
