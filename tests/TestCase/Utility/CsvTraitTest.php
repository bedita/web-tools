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
     * @inheritDoc
     */
    protected $_defaultConfig = [
        'csv' => [
            'delimiter' => ',',
            'enclosure' => '"',
            'escape' => '"',
        ],
    ];

    /**
     * Test `readCsv` method, NotFoundException
     *
     * @return void
     * @covers ::readCsv()
     */
    public function testReadNotFound(): void
    {
        $path = sprintf('%s/tests/files/not-found.csv', getcwd());
        $expected = new NotFoundException(sprintf('File not found: %s', $path));
        $this->expectException(get_class($expected));
        $this->expectExceptionCode($expected->getCode());
        $this->expectExceptionMessage($expected->getMessage());
        $actual = [];
        foreach ($this->readCsv($path) as $row) {
            $actual[] = $row;
        }
        static::assertSame('Generator', get_class($actual[0]));
    }

    /**
     * Test `readCsv` method
     *
     * @return void
     * @covers ::readCsv()
     */
    public function testReadCsv(): void
    {
        $path = sprintf('%s/tests/files/test.csv', getcwd());
        $expected = [
            ['title' => 'The Great Gatsby', 'author' => 'Francis Scott Fitzgerald'],
            ['title' => 'Moby-Dick', 'author' => 'Herman Melville'],
            ['title' => 'Ulysses', 'author' => 'James Joyce'],
            ['title' => 'Hearth of Darkness', 'author' => 'Joseph Conrad'],
        ];
        $actual = [];
        foreach ($this->readCsv($path) as $row) {
            $actual[] = $row;
        }
        static::assertSame($expected, $actual);
    }
}
