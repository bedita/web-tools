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
namespace BEdita\WebTools;

use BEdita\WebTools\Command\CacheClearallCommand;
use Cake\Console\CommandCollection;
use Cake\Core\BasePlugin;
use Cake\Core\PluginApplicationInterface;

/**
 * Plugin class for BEdita\WebTools.
 */
class Plugin extends BasePlugin
{
    /**
     * Load routes or not
     *
     * @var bool
     */
    protected bool $routesEnabled = false;

    /**
     * Use `cache clear_all` from BEdita\WebTools\Command\CacheClearallCommand
     *
     * @param \Cake\Console\CommandCollection $commands Console commands.
     * @return \Cake\Console\CommandCollection
     */
    public function console(CommandCollection $commands): CommandCollection
    {
        parent::console($commands);
        $commands->remove('cache clear_all');
        $commands->add('cache clear_all', CacheClearallCommand::class);

        return $commands;
    }

    /**
     * @inheritDoc
     */
    public function bootstrap(PluginApplicationInterface $app): void
    {
        // Call parent to load bootstrap from files.
        parent::bootstrap($app);

        // Load more plugins here
        $app->addPlugin('Cake/TwigView');
    }
}
