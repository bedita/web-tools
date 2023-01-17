<?php
declare(strict_types=1);

/**
 * BEdita, API-first content management framework
 * Copyright 2023 Atlas Srl, Chialab Srl
 *
 * This file is part of BEdita: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * See LICENSE.LGPL or <http://gnu.org/licenses/lgpl-3.0.html> for more details.
 */
namespace BEdita\WebTools\Test\TestCase\Utility;

use BEdita\WebTools\Utility\CsvTrait;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\NotFoundException;
use Cake\TestSuite\TestCase;

/**
 * {@see \BEdita\WebTools\Utility\CsvTrait} Test Case
 *
 * @coversDefaultClass \BEdita\WebTools\Utility\CsvTrait
 */
class CsvTraitTest extends TestCase
{
    use CsvTrait;

    /**
     * Test `read` method, NotFoundException
     *
     * @return void
     * @covers ::read()
     */
    public function testReadFileNotFound(): void
    {
        $path = './file-that-does-not-exist.csv';
        $expected = new NotFoundException(sprintf('Unable to open file in read mode: %s', $path));
        $this->expectException(get_class($expected));
        $this->expectExceptionCode($expected->getCode());
        $this->expectExceptionMessage($expected->getMessage());
        $this->read($path);
    }

    /**
     * Test `read` method, BadRequestException
     *
     * @return void
     * @covers ::read()
     */
    public function testReadBadRequest(): void
    {
        $path = sprintf('%s/tests/files/bad-file.csv', getcwd());
        $expected = new BadRequestException(sprintf('Unable to get csv data for file: %s', $path));
        $this->expectException(get_class($expected));
        $this->expectExceptionCode($expected->getCode());
        $this->expectExceptionMessage($expected->getMessage());
        $this->read($path);
    }

    /**
     * Test `read` method
     *
     * @return void
     * @covers ::read()
     */
    public function testRead(): void
    {
        $path = sprintf('%s/tests/files/test.csv', getcwd());
        $expected = [
            ['title' => 'The Great Gatsby', 'author' => 'Francis Scott Fitzgerald'],
            ['title' => 'Moby-Dick', 'author' => 'Herman Melville'],
            ['title' => 'Ulysses', 'author' => 'James Joyce'],
            ['title' => 'Hearth of Darkness', 'author' => 'Joseph Conrad'],
        ];
        $actual = [];
        foreach ($this->read($path) as $row) {
            $actual[] = $row;
        }
        static::assertSame($expected, $actual);
    }
}
