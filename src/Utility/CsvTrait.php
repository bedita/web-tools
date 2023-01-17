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
namespace BEdita\WebTools\Utility;

use Cake\Core\InstanceConfigTrait;
use Cake\Http\Exception\BadRequestException;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

/**
 * Trait for share Csv stuff.
 */
trait CsvTrait
{
    use InstanceConfigTrait;

    /**
     * Progressively read a CSV file, line by line.
     *
     * @param string $path Path to CSV file.
     * @return \Generator<array<string, string>>
     */
    public function read($path): \Generator
    {
        $fh = fopen($path, 'rb');
        if (!$fh) {
            throw new FileNotFoundException(sprintf('Unable to open file in read mode: %s', $path));
        }
        $options = $this->getConfig('csv');
        $delimiter = $options['delimiter'];
        $enclosure = $options['enclosure'];
        $escape = $options['escape'];
        flock($fh, LOCK_SH);
        $header = fgetcsv($fh, null, $delimiter, $enclosure, $escape);
        if ($header === false) {
            throw new BadRequestException(sprintf('Unable to get csv data for file: %s', $path));
        }
        $i = 0;
        while (($row = fgetcsv($fh, null, $delimiter, $enclosure, $escape)) !== false) {
            yield array_combine($header, $row);
            $i++;
        }
        flock($fh, LOCK_UN);
        fclose($fh);
    }
}
