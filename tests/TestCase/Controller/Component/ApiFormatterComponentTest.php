<?php
declare(strict_types=1);

namespace BEdita\WebTools\Test\TestCase\Controller\Component;

use BEdita\WebTools\Controller\Component\ApiFormatterComponent;
use Cake\Controller\ComponentRegistry;
use Cake\I18n\Number;
use Cake\TestSuite\TestCase;

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
     * Provider for `testAddObjectsStream` method
     *
     * @return array
     */
    public function addObjectsStreamProvider(): array
    {
        $streams = [
            ['id' => '11', 'type' => 'streams', 'attributes' => ['key' => 111], 'meta' => ['file_size' => 1024 ** 1]],
            ['id' => '22', 'type' => 'streams', 'attributes' => ['key' => 222], 'meta' => ['file_size' => 1024 ** 2]],
            ['id' => '33', 'type' => 'streams', 'attributes' => ['key' => 333], 'meta' => ['file_size' => 1024 ** 3]],
        ];
        $expectedStreams = [
            ['id' => '11', 'type' => 'streams', 'attributes' => ['key' => 111], 'meta' => ['file_size' => Number::toReadableSize(1024 ** 1)]],
            ['id' => '22', 'type' => 'streams', 'attributes' => ['key' => 222], 'meta' => ['file_size' => Number::toReadableSize(1024 ** 2)]],
            ['id' => '33', 'type' => 'streams', 'attributes' => ['key' => 333], 'meta' => ['file_size' => Number::toReadableSize(1024 ** 3)]],
        ];

        return [
            'empty data' => [
                [],
                [],
            ],
            'empty included' => [
                ['data' => [['id' => 1]]],
                ['data' => [['id' => 1]]],
            ],
            'no streams' => [
                ['data' => [['id' => 1]], 'included' => [['id' => 2, 'type' => 'dummies']]],
                ['data' => [['id' => 1]], 'included' => [['id' => 2, 'type' => 'dummies']]],
            ],
            'streams' => [
                [
                    'data' => [
                        ['id' => 1, 'type' => 'dummies', 'relationships' => ['streams' => ['data' => [$streams[0]]]]],
                        ['id' => 2, 'type' => 'dummies', 'relationships' => ['streams' => ['data' => [$streams[1]]]]],
                        ['id' => 3, 'type' => 'dummies', 'relationships' => ['streams' => ['data' => [$streams[2]]]]],
                        ['id' => 4, 'type' => 'dummies'],
                    ],
                    'included' => $streams,
                ],
                [
                    'data' => [
                        [
                            'id' => 1,
                            'type' => 'dummies',
                            'relationships' => ['streams' => ['data' => [$streams[0]]]],
                            'stream' => $expectedStreams[0],
                        ],
                        [
                            'id' => 2,
                            'type' => 'dummies',
                            'relationships' => ['streams' => ['data' => [$streams[1]]]],
                            'stream' => $expectedStreams[1],
                        ],
                        [
                            'id' => 3,
                            'type' => 'dummies',
                            'relationships' => ['streams' => ['data' => [$streams[2]]]],
                            'stream' => $expectedStreams[2],
                        ],
                        ['id' => 4, 'type' => 'dummies'],
                    ],
                    'included' => $streams,
                ],
            ],
        ];
    }

    /**
     * Test `addObjectsStream` method
     *
     * @param array $response The response data for test
     * @param array $expected The expected resulting data
     * @return void
     * @covers ::addObjectsStream()
     * @covers ::extractFromIncluded()
     * @dataProvider addObjectsStreamProvider()
     */
    public function testAddObjectsStream(array $response, array $expected): void
    {
        $actual = $this->ApiFormatter->addObjectsStream($response);
        static::assertEquals($expected, $actual);
    }

    /**
     * Provider for `testEmbedIncluded` method
     *
     * @return array
     */
    public function embedIncludedProvider(): array
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
            'empty data' => [
                [],
                [],
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
     * @dataProvider embedIncludedProvider()
     */
    public function testEmbedIncluded(array $response, array $expected): void
    {
        $actual = $this->ApiFormatter->embedIncluded($response);
        static::assertEquals($expected, $actual);
    }
}
