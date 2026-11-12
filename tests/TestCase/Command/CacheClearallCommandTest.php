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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;

/**
 * {@see BEdita\WebTools\Command\CacheClearallCommand} Test Case
 */
#[CoversClass(CacheClearallCommand::class)]
#[CoversMethod(CacheClearallCommand::class, 'execute')]
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
     */
    public function testExecute(): void
    {
        $path = CACHE . 'twig_view';

        // create directory with subdirectories and files to test cache clearing
        mkdir($path . '/subdir1', 0777, true);
        file_put_contents($path . '/subdir1/file1.txt', 'test');
        mkdir($path . '/subdir2', 0777, true);
        file_put_contents($path . '/subdir2/file2.txt', 'test');
        Router::resetRoutes();
        $this->exec('cache clear_all');
        $this->assertExitSuccess();
        $this->assertOutputContains('<success>Cleared twig cache</success>');
        // check that the directories and files have been removed
        $this->assertDirectoryDoesNotExist($path . '/subdir1');
        $this->assertDirectoryDoesNotExist($path . '/subdir2');
        // main directory should still exist
        $this->assertDirectoryExists($path);

        rmdir($path);
        $this->exec('cache clear_all');
        $this->assertExitSuccess();
        $this->assertOutputContains('<warning>Twig cache path not found: ' . $path . '</warning>');
    }
}
