<?php
declare(strict_types=1);

namespace BEdita\WebTools\Test\TestCase\Controller\Component;

use BEdita\WebTools\Controller\Component\ApiFormatterComponent;
use Cake\Controller\ComponentRegistry;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * {@see \BEdita\WebTools\Controller\Component\ApiFormatterComponent} Test Case
 *
 * @coversDefaultClass \BEdita\WebTools\Controller\Component\ApiFormatterComponent
 */
class ApiFormatterComponentTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Controller\Component\ApiFormatter
     */
    public $ApiFormatter;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $registry = new ComponentRegistry();
        $this->ApiFormatter = new ApiFormatterComponent($registry);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->ApiFormatter);
        parent::tearDown();
    }

    /**
     * Provider for `testEmbedIncluded` method
     *
     * @return array
     */
    public static function embedIncludedProvider(): array
    {
        $gustavo = ['id' => 1, 'type' => 'persons', 'attributes' => ['name' => 'Gustavo'], 'relationships' => [['chief_of' => [['id' => 777, 'type' => 'universes']]]]];
        $tv = ['id' => 2, 'type' => 'things', 'attributes' => ['name' => 'Television'], 'relationships' => [['part_of' => [['id' => 888, 'type' => 'furnitures']]]]];
        $relationships = [
            'a' => [['id' => 1, 'type' => 'persons']],
            'b' => [['id' => 2, 'type' => 'things']],
        ];
        $relationshipsWithData = [
            'a' => ['data' => [['id' => 1, 'type' => 'persons']]],
            'b' => ['data' => [['id' => 2, 'type' => 'things']]],
        ];
        $relationshipsWithDataExpected = [
            'a' => ['data' => [$gustavo]],
            'b' => ['data' => [$tv]],
        ];

        return [
            'no data' => [
                ['something'],
                ['something'],
            ],
            'empty data' => [
                ['data' => [], 'something'],
                ['data' => [], 'something'],
            ],
            'empty included' => [
                ['data' => [['id' => 1]]],
                ['data' => [['id' => 1]]],
            ],
            'non numeric keys, no data' => [
                [
                    'data' => ['relationships' => $relationships],
                    'included' => [$gustavo, $tv],
                ],
                [
                    'data' => ['relationships' => $relationships],
                    'included' => [$gustavo, $tv],
                ],
            ],
            'non numeric keys + data' => [
                [
                    'data' => ['relationships' => $relationshipsWithData],
                    'included' => [$gustavo, $tv],
                ],
                [
                    'data' => ['relationships' => $relationshipsWithDataExpected],
                    'included' => [$gustavo, $tv],
                ],
            ],
            'numeric keys + data' => [
                [
                    'data' => [
                        [
                            'id' => '12',
                            'type' => 'images',
                            'attributes' => [
                                'title' => 'test',
                            ],
                            'relationships' => [
                                'object' => [
                                    'links' => [],
                                    'data' => [
                                        'id' => '999999',
                                        'type' => 'dummies',
                                        'attributes' => [
                                            'title' => 'dummy object',
                                        ],
                                    ],
                                ],
                                'streams' => [
                                    'links' => [],
                                    'data' => [
                                        [
                                            'id' => 'af829cbb-c570-4282-94c3-782cf315983a',
                                            'type' => 'streams',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'included' => [
                        [
                            'id' => '999999',
                            'type' => 'dummies',
                            'attributes' => [
                                'title' => 'dummy object',
                            ],
                        ],
                        [
                            'id' => 'af829cbb-c570-4282-94c3-782cf315983a',
                            'type' => 'streams',
                            'attributes' => [
                                'file_name' => 'test.jpg',
                                'mime_type' => 'image/jpeg',
                            ],
                        ],
                    ],
                ],
                [
                    'data' => [
                        [
                            'id' => '12',
                            'type' => 'images',
                            'attributes' => [
                                'title' => 'test',
                            ],
                            'relationships' => [
                                'object' => [
                                    'links' => [],
                                    'data' => [
                                        'id' => '999999',
                                        'type' => 'dummies',
                                        'attributes' => [
                                            'title' => 'dummy object',
                                        ],
                                    ],
                                ],
                                'streams' => [
                                    'links' => [],
                                    'data' => [
                                        [
                                            'id' => 'af829cbb-c570-4282-94c3-782cf315983a',
                                            'type' => 'streams',
                                            'attributes' => [
                                                'file_name' => 'test.jpg',
                                                'mime_type' => 'image/jpeg',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'included' => [
                        [
                            'id' => '999999',
                            'type' => 'dummies',
                            'attributes' => [
                                'title' => 'dummy object',
                            ],
                        ],
                        [
                            'id' => 'af829cbb-c570-4282-94c3-782cf315983a',
                            'type' => 'streams',
                            'attributes' => [
                                'file_name' => 'test.jpg',
                                'mime_type' => 'image/jpeg',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Test `embedIncluded` method
     *
     * @param array $response The response data for test
     * @param array $expected The expected resulting data
     * @return void
     * @covers ::embedIncluded()
     * @covers ::addIncluded()
     * @covers ::extractFromIncluded()
     */
    #[DataProvider('embedIncludedProvider')]
    public function testEmbedIncluded(array $response, array $expected): void
    {
        $actual = $this->ApiFormatter->embedIncluded($response);
        static::assertEquals($expected, $actual);
    }

    /**
     * Data provider for `testReplaceWithTranslation()`.
     *
     * @return array
     */
    public static function replaceWithTranslationProvider(): array
    {
        return [
            'empty response data' => [
                [],
                [],
                'en',
            ],
            'single object without translation' => [
                [
                    'data' => [
                        'id' => 1,
                        'type' => 'documents',
                        'attributes' => [
                            'lang' => 'it',
                            'title' => 'Ciao',
                            'description' => 'Mondo',
                        ],
                        'relationships' => [],
                    ],
                ],
                [
                    'data' => [
                        'id' => 1,
                        'type' => 'documents',
                        'attributes' => [
                            'lang' => 'it',
                            'title' => 'Ciao',
                            'description' => 'Mondo',
                        ],
                        'relationships' => [],
                    ],
                ],
                'en',
            ],
            'single object with translation' => [
                [
                    'data' => [
                        'id' => 1,
                        'type' => 'documents',
                        'attributes' => [
                            'lang' => 'it',
                            'title' => 'Hello',
                            'description' => 'World',
                        ],
                        'relationships' => [
                            'translations' => [
                                'links' => [],
                                'data' => [
                                    [
                                        'id' => 10,
                                        'type' => 'translations',
                                        'attributes' => [
                                            'lang' => 'en',
                                            'object_id' => 1,
                                            'translated_fields' => [
                                                'title' => 'Hello',
                                                'description' => 'World',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'data' => [
                        'id' => 1,
                        'type' => 'documents',
                        'attributes' => [
                            'lang' => 'it',
                            'title' => 'Ciao',
                            'description' => 'Mondo',
                        ],
                        'relationships' => [
                            'translations' => [
                                'links' => [],
                                'data' => [
                                    [
                                        'id' => 10,
                                        'type' => 'translations',
                                        'attributes' => [
                                            'lang' => 'en',
                                            'object_id' => 1,
                                            'translated_fields' => [
                                                'title' => 'Hello',
                                                'description' => 'World',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'en',
            ],
            'single object and request the object main lang' => [
                [
                    'data' => [
                        'id' => 1,
                        'type' => 'documents',
                        'attributes' => [
                            'lang' => 'it',
                            'title' => 'Ciao',
                            'description' => 'Mondo',
                        ],
                        'relationships' => [
                            'translations' => [
                                'links' => [],
                                'data' => [
                                    [
                                        'id' => 10,
                                        'type' => 'translations',
                                        'attributes' => [
                                            'lang' => 'en',
                                            'object_id' => 1,
                                            'translated_fields' => [
                                                'title' => 'Hello',
                                                'description' => 'World',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'data' => [
                        'id' => 1,
                        'type' => 'documents',
                        'attributes' => [
                            'lang' => 'it',
                            'title' => 'Ciao',
                            'description' => 'Mondo',
                        ],
                        'relationships' => [
                            'translations' => [
                                'links' => [],
                                'data' => [
                                    [
                                        'id' => 10,
                                        'type' => 'translations',
                                        'attributes' => [
                                            'lang' => 'en',
                                            'object_id' => 1,
                                            'translated_fields' => [
                                                'title' => 'Hello',
                                                'description' => 'World',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'it',
            ],
            'multiple objects and request the object main lang' => [
                [
                    'data' => [
                        [
                            'id' => 1,
                            'type' => 'documents',
                            'attributes' => [
                                'lang' => 'it',
                                'title' => 'Hello',
                                'description' => 'World',
                            ],
                            'relationships' => [
                                'translations' => [
                                    'links' => [],
                                    'data' => [
                                        [
                                            'id' => 10,
                                            'type' => 'translations',
                                            'attributes' => [
                                                'lang' => 'en',
                                                'object_id' => 1,
                                                'translated_fields' => [
                                                    'title' => 'Hello',
                                                    'description' => 'World',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'id' => 1,
                            'type' => 'documents',
                            'attributes' => [
                                'lang' => 'it',
                                'title' => 'This is the title',
                                'description' => 'La descrizione',
                            ],
                            'relationships' => [
                                'translations' => [
                                    'links' => [],
                                    'data' => [
                                        [
                                            'id' => 10,
                                            'type' => 'translations',
                                            'attributes' => [
                                                'lang' => 'en',
                                                'object_id' => 1,
                                                'translated_fields' => [
                                                    'title' => 'This is the title',
                                                    'description' => '',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'data' => [
                        [
                            'id' => 1,
                            'type' => 'documents',
                            'attributes' => [
                                'lang' => 'it',
                                'title' => 'Ciao',
                                'description' => 'Mondo',
                            ],
                            'relationships' => [
                                'translations' => [
                                    'links' => [],
                                    'data' => [
                                        [
                                            'id' => 10,
                                            'type' => 'translations',
                                            'attributes' => [
                                                'lang' => 'en',
                                                'object_id' => 1,
                                                'translated_fields' => [
                                                    'title' => 'Hello',
                                                    'description' => 'World',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'id' => 1,
                            'type' => 'documents',
                            'attributes' => [
                                'lang' => 'it',
                                'title' => 'Questo è il titolo',
                                'description' => 'La descrizione',
                            ],
                            'relationships' => [
                                'translations' => [
                                    'links' => [],
                                    'data' => [
                                        [
                                            'id' => 10,
                                            'type' => 'translations',
                                            'attributes' => [
                                                'lang' => 'en',
                                                'object_id' => 1,
                                                'translated_fields' => [
                                                    'title' => 'This is the title',
                                                    'description' => '',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'en',
            ],
            'multiple objects with translation' => [
                [
                    'data' => [
                        [
                            'id' => 1,
                            'type' => 'documents',
                            'attributes' => [
                                'lang' => 'it',
                                'title' => 'Ciao',
                                'description' => 'Mondo',
                            ],
                            'relationships' => [
                                'translations' => [
                                    'links' => [],
                                    'data' => [
                                        [
                                            'id' => 10,
                                            'type' => 'translations',
                                            'attributes' => [
                                                'lang' => 'en',
                                                'object_id' => 1,
                                                'translated_fields' => [
                                                    'title' => 'Hello',
                                                    'description' => 'World',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'id' => 1,
                            'type' => 'documents',
                            'attributes' => [
                                'lang' => 'it',
                                'title' => 'Questo è il titolo',
                                'description' => 'La descrizione',
                            ],
                            'relationships' => [
                                'translations' => [
                                    'links' => [],
                                    'data' => [
                                        [
                                            'id' => 10,
                                            'type' => 'translations',
                                            'attributes' => [
                                                'lang' => 'en',
                                                'object_id' => 1,
                                                'translated_fields' => [
                                                    'title' => 'This is the title',
                                                    'description' => '',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'data' => [
                        [
                            'id' => 1,
                            'type' => 'documents',
                            'attributes' => [
                                'lang' => 'it',
                                'title' => 'Ciao',
                                'description' => 'Mondo',
                            ],
                            'relationships' => [
                                'translations' => [
                                    'links' => [],
                                    'data' => [
                                        [
                                            'id' => 10,
                                            'type' => 'translations',
                                            'attributes' => [
                                                'lang' => 'en',
                                                'object_id' => 1,
                                                'translated_fields' => [
                                                    'title' => 'Hello',
                                                    'description' => 'World',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'id' => 1,
                            'type' => 'documents',
                            'attributes' => [
                                'lang' => 'it',
                                'title' => 'Questo è il titolo',
                                'description' => 'La descrizione',
                            ],
                            'relationships' => [
                                'translations' => [
                                    'links' => [],
                                    'data' => [
                                        [
                                            'id' => 10,
                                            'type' => 'translations',
                                            'attributes' => [
                                                'lang' => 'en',
                                                'object_id' => 1,
                                                'translated_fields' => [
                                                    'title' => 'This is the title',
                                                    'description' => '',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'it',
            ],
            'multiple objects with and without translation' => [
                [
                    'data' => [
                        [
                            'id' => 1,
                            'type' => 'documents',
                            'attributes' => [
                                'lang' => 'it',
                                'title' => 'Hello',
                                'description' => 'World',
                            ],
                            'relationships' => [
                                'translations' => [
                                    'links' => [],
                                    'data' => [
                                        [
                                            'id' => 10,
                                            'type' => 'translations',
                                            'attributes' => [
                                                'lang' => 'en',
                                                'object_id' => 1,
                                                'translated_fields' => [
                                                    'title' => 'Hello',
                                                    'description' => 'World',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'id' => 1,
                            'type' => 'documents',
                            'attributes' => [
                                'lang' => 'it',
                                'title' => 'Questo è il titolo',
                                'description' => 'La descrizione',
                            ],
                            'relationships' => [],
                        ],
                    ],
                ],
                [
                    'data' => [
                        [
                            'id' => 1,
                            'type' => 'documents',
                            'attributes' => [
                                'lang' => 'it',
                                'title' => 'Ciao',
                                'description' => 'Mondo',
                            ],
                            'relationships' => [
                                'translations' => [
                                    'links' => [],
                                    'data' => [
                                        [
                                            'id' => 10,
                                            'type' => 'translations',
                                            'attributes' => [
                                                'lang' => 'en',
                                                'object_id' => 1,
                                                'translated_fields' => [
                                                    'title' => 'Hello',
                                                    'description' => 'World',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'id' => 1,
                            'type' => 'documents',
                            'attributes' => [
                                'lang' => 'it',
                                'title' => 'Questo è il titolo',
                                'description' => 'La descrizione',
                            ],
                            'relationships' => [],
                        ],
                    ],
                ],
                'en',
            ],
        ];
    }

    /**
     * Test `replaceWithTranslation()` method.
     *
     * @param array $expected The expected data
     * @param array $response The response data
     * @param string $lang The lang requested
     * @return void
     * @covers ::replaceWithTranslation()
     * @covers ::extractTranslatedFields()
     */
    #[DataProvider('replaceWithTranslationProvider')]
    public function testReplaceWithTranslation(array $expected, array $response, string $lang): void
    {
        $actual = $this->ApiFormatter->replaceWithTranslation($response, $lang);
        static::assertEquals($expected, $actual);
    }

    /**
     * Test `cleanResponse()` method.
     *
     * @return void
     * @covers ::cleanResponse()
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
        $actual = $this->ApiFormatter->cleanResponse($response);
        $expected = [
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
