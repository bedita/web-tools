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
namespace BEdita\WebTools\Test\TestCase\Utility;

use BEdita\WebTools\Utility\ApiTools;
use Cake\TestSuite\TestCase;

/**
 * {@see \BEdita\WebTools\Utility\ApiTools} Test Case
 *
 * @coversDefaultClass \BEdita\WebTools\Utility\ApiTools
 */
class ApiToolsTest extends TestCase
{
    /**
     * Test clean response
     *
     * @return void
     * @covers ::cleanResponse()
     * @covers ::recursiveRemoveKey()
     * @covers ::removeAttributes()
     * @covers ::removeIncluded()
     * @covers ::removeLinks()
     * @covers ::removeRelationships()
     * @covers ::removeSchema()
     */
    public function testCleanResponse(): void
    {
        $response = [
            'data' => [
                [
                    'id' => 1,
                    'attributes' => [
                        'uname' => 'gustavo',
                        'title' => 'gustavo supporto',
                        'name' => 'gustavo',
                        'surname' => 'supporto',
                        'extra' => ['some' => 'thing'],
                    ],
                    'links' => [
                        'self' => 'https://api.example.org/users/1',
                    ],
                    'relationships' => [
                        'roles' => [
                            'links' => [
                                'self' => 'https://api.example.org/users/1/relationships/roles',
                                'related' => 'https://api.example.org/users/1/roles',
                            ],
                        ],
                    ],
                ],
            ],
            'meta' => [
                'pagination' => [
                    'page' => 1,
                    'page_count' => 1,
                    'page_items' => 1,
                    'page_size' => 20,
                    'count' => 1,
                ],
                'schema' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => [
                            'type' => 'integer',
                        ],
                        'type' => [
                            'type' => 'string',
                        ],
                        'attributes' => [
                            'type' => 'object',
                        ],
                        'links' => [
                            'type' => 'object',
                        ],
                        'relationships' => [
                            'type' => 'object',
                        ],
                    ],
                    'required' => ['id', 'type', 'attributes'],
                ],
            ],
            'included' => [
                [
                    'id' => 1,
                    'type' => 'roles',
                    'attributes' => [
                        'name' => 'admin',
                        'extra' => ['any' => 'thing'],
                    ],
                ],
            ],
            'links' => [
                'self' => 'https://api.example.org/users',
                'first' => 'https://api.example.org/users?page=1',
                'last' => 'https://api.example.org/users?page=1',
            ],
        ];
        $actual = ApiTools::cleanResponse($response);
        $expected = [
            'data' => [
                [
                    'id' => 1,
                    'attributes' => [
                        'uname' => 'gustavo',
                        'title' => 'gustavo supporto',
                        'name' => 'gustavo',
                        'surname' => 'supporto',
                    ],
                ],
            ],
            'meta' => [
                'pagination' => [
                    'page' => 1,
                    'page_count' => 1,
                    'page_items' => 1,
                    'page_size' => 20,
                    'count' => 1,
                ],
            ],
        ];
        static::assertEquals($expected, $actual);
    }
}
