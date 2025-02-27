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
namespace BEdita\WebTools\Test\TestCase\Command;

use BEdita\WebTools\Command\CacheClearallCommand;
use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;

/**
 * {@see BEdita\WebTools\Command\CacheClearallCommand} Test Case
 *
 * @coversDefaultClass \BEdita\WebTools\Command\CacheClearallCommand
 */
class CacheClearallCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;

    /**
     * The command used in test
     *
     * @var \BEdita\WebTools\Command\CacheClearallCommand|null
     */
    protected ?CacheClearallCommand $command = null;

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->command = new CacheClearallCommand();
    }

    /**
     * Test execute method
     *
     * @return void
     * @covers ::execute()
     */
    public function testExecute(): void
    {
        $path = CACHE . 'twig_view';
        $this->exec('cache clear_all');
        $this->assertExitSuccess();
        $this->assertOutputContains('<warning>Twig cache path not found: ' . $path . '</warning>');

        // create directory to be deleted
        mkdir($path, 0777, true);
        Router::resetRoutes();
        $this->exec('cache clear_all');
        $this->assertExitSuccess();
        $this->assertOutputContains('<success>Cleared twig cache</success>');
    }
}
