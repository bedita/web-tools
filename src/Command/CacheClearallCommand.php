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
namespace BEdita\WebTools\Command;

use Cake\Command\CacheClearallCommand as BaseCommand;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;

/**
 * Extend `CacheClearallCommand` to remove Twig compiled files.
 */
class CacheClearallCommand extends BaseCommand
{
    /**
     * {@inheritDoc}
     *
     * Add `twig` compiled files removal step.
     */
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $path = CACHE . 'twig_view';
        if (!file_exists($path)) {
            $io->out("<warning>Twig cache path not found: {$path}</warning>");

            return parent::execute($args, $io);
        }
        unlink($path);
        $io->out('<success>Cleared twig cache</success>');

        return parent::execute($args, $io);
    }
}
