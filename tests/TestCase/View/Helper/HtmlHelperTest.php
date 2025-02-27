<?php
declare(strict_types=1);

/**
 * BEdita, API-first content management framework
 * Copyright 2018 ChannelWeb Srl, Chialab Srl
 *
 * This file is part of BEdita: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * See LICENSE.LGPL or <http://gnu.org/licenses/lgpl-3.0.html> for more details.
 */
namespace BEdita\WebTools\Test\TestCase\View\Helper;

use BEdita\WebTools\Utility\Asset\AssetStrategyInterface;
use BEdita\WebTools\Utility\Asset\Strategy\EntrypointsStrategy;
use BEdita\WebTools\Utility\Asset\Strategy\RevManifestStrategy;
use BEdita\WebTools\Utility\AssetsRevisions;
use BEdita\WebTools\View\Helper\HtmlHelper;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;
use Cake\View\View;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * {@see \BEdita\WebTools\View\Helper\HtmlHelper} Test Case
 *
 * @coversDefaultClass \BEdita\WebTools\View\Helper\HtmlHelper
 */
class HtmlHelperTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \BEdita\WebTools\View\Helper\HtmlHelper
     */
    public $Html;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        parent::setUp();

        // create helper
        $this->Html = new HtmlHelper(new View());
    }

    /**
     * @inheritDoc
     */
    public function tearDown(): void
    {
        unset($this->Html);
        AssetsRevisions::clearStrategy();

        parent::tearDown();
    }

    /**
     * Data provider for `testTitle` test case.
     *
     * @return array
     */
    public static function titleProvider(): array
    {
        return [
            'empty string' => [
                null,
                null,
                null,
                '',
            ],
            '_title' => [
                null,
                null,
                'My title',
                'My title',
            ],
            'title from controller' => [
                'my_controller',
                null,
                null,
                'My Controller',
            ],
            'title from controller, action 1' => [
                'my_controller',
                'my_action',
                null,
                'My Controller - My Action',
            ],
            'title from controller, action 2' => [
                'my_controller',
                'my_action',
                'My title',
                'My title',
            ],
        ];
    }

    /**
     * Test `title` method
     *
     * @covers ::title()
     * @param string|null $controllerName The controller name
     * @param string|null $actionName The action name
     * @param string|null $viewVarTitle The title
     * @param string $expected The expected title
     * @return void
     */
    #[DataProvider('titleProvider')]
    public function testTitle(?string $controllerName, ?string $actionName, ?string $viewVarTitle, string $expected): void
    {
        $request = new ServerRequest([
            'params' => [
                'controller' => $controllerName,
                'action' => $actionName,
            ],
        ]);
        $Html = new HtmlHelper(new View($request));
        $Html->getView()->set('_title', $viewVarTitle);
        $actual = $Html->title();
        static::assertEquals($expected, $actual);
    }

    /**
     * Data provider for `testMetaDescription` test case.
     *
     * @return array
     */
    public static function metaDescriptionProvider(): array
    {
        return [
            'null description' => [
                null,
                '',
            ],
            'empty description' => [
                '',
                '',
            ],
            'dummy description' => [
                'dummy',
                '<meta name="description" content="dummy">',
            ],
            'description with special chars and tags' => [
                'dummy <> & dummy',
                '<meta name="description" content="dummy  &amp;amp; dummy">',
            ],
        ];
    }

    /**
     * Test `metaDescription` method
     *
     * @covers ::metaDescription()
     * @param string|null $description The description
     * @param string $expected The expected meta description
     * @return void
     */
    #[DataProvider('metaDescriptionProvider')]
    public function testMetaDescription(?string $description, string $expected): void
    {
        $actual = $this->Html->metaDescription($description);
        static::assertEquals($expected, $actual);
    }

    /**
     * Data provider for `testMetaAuthor` test case.
     *
     * @return array
     */
    public static function metaAuthorProvider(): array
    {
        return [
            'null creator' => [
                null,
                '',
            ],
            'empty creator' => [
                '',
                '',
            ],
            'dummy creator' => [
                'dummy',
                '<meta name="author" content="dummy">',
            ],
            'creator with special chars and tags' => [
                'dummy <> & dummy',
                '<meta name="author" content="dummy &amp;lt;&amp;gt; &amp;amp; dummy">',
            ],
        ];
    }

    /**
     * Test `metaAuthor` method
     *
     * @covers ::metaAuthor()
     * @param string|null $creator The content creator
     * @param string $expected The expected meta content author
     * @return void
     */
    #[DataProvider('metaAuthorProvider')]
    public function testMetaAuthor(?string $creator, string $expected): void
    {
        $actual = $this->Html->metaAuthor($creator);
        static::assertEquals($expected, $actual);
    }

    /**
     * Data provider for `testMetaCss` test case.
     *
     * @return array
     */
    public static function metaCssProvider(): array
    {
        return [
            'empty docType' => [
                '',
                '<meta http-equiv="Content-Style-Type" content="text/css">',
            ],
            'html5 docType' => [
                'html5',
                '',
            ],
        ];
    }

    /**
     * Test `metaCss` method
     *
     * @covers ::metaCss()
     * @param string $docType The doc type
     * @param string $expected The expected meta content author
     * @return void
     */
    #[DataProvider('metaCssProvider')]
    public function testMetaCss(string $docType, string $expected): void
    {
        $actual = $this->Html->metaCss($docType);
        static::assertEquals($expected, $actual);
    }

    /**
     * Data provider for `testMetaGenerator` test case.
     *
     * @return array
     */
    public static function metaGeneratorProvider(): array
    {
        return [
            'empty project and version 1' => [
                [],
                '',
            ],
            'empty project and version 2' => [
                [
                    'name' => '',
                    'version' => '',
                ],
                '',
            ],
            'only project' => [
                [
                    'name' => 'Dummy',
                ],
                '<meta name="generator" content="Dummy">',
            ],
            'project and version' => [
                [
                    'name' => 'Dummy',
                    'version' => '1.0',
                ],
                '<meta name="generator" content="Dummy 1.0">',
            ],
        ];
    }

    /**
     * Test `metaGenerator` method
     *
     * @covers ::metaGenerator()
     * @param array $project The project data ('name', 'version')
     * @param string $expected The expected meta content author
     * @return void
     */
    #[DataProvider('metaGeneratorProvider')]
    public function testMetaGenerator(array $project, string $expected): void
    {
        $actual = $this->Html->metaGenerator($project);
        static::assertEquals($expected, $actual);
    }

    /**
     * Data provider for `testMetaAll` test case.
     *
     * @return array
     */
    public static function metaAllProvider(): array
    {
        return [
            'empty data' => [
                [],
                '<meta http-equiv="Content-Style-Type" content="text/css">',
            ],
            'full data' => [
                [
                    'viewport' => 'width=device-width, initial-scale=1.0',
                    'msapplication-TileColor' => '#009cc7',
                    'theme-color' => '#ABC000',
                    'description' => 'dummy description',
                    'author' => 'gustavo',
                    'project' => [
                        'name' => 'my dummy project',
                        'version' => '2.0',
                    ],
                ],
                '<meta name="description" content="dummy description"><meta name="author" content="gustavo"><meta http-equiv="Content-Style-Type" content="text/css"><meta name="generator" content="my dummy project 2.0"><meta name="viewport" content="width=device-width, initial-scale=1.0"><meta name="msapplication-TileColor" content="#009cc7"><meta name="theme-color" content="#ABC000">',
            ],
        ];
    }

    /**
     * Test `metaAll` method
     *
     * @covers ::metaAll()
     * @param array $data The data for meta
     * @param string $expected The expected meta html
     * @return void
     */
    #[DataProvider('metaAllProvider')]
    public function testMetaAll(array $data, string $expected): void
    {
        $actual = $this->Html->metaAll($data);
        static::assertEquals($expected, $actual);
    }

    /**
     * Data provider for `testMetaOpenGraph` test case.
     *
     * @return array
     */
    public static function metaOpenGraphProvider(): array
    {
        return [
            'empty data' => [
                [],
                '',
            ],
            'url + title + description + image' => [
                [
                    'url' => 'https://example.com',
                    'title' => 'dummy',
                    'description' => 'a dummy data for test',
                    'image' => 'an image',
                ],
                '<meta property="og:url" content="https://example.com"><meta property="og:title" content="dummy"><meta property="og:description" content="a dummy data for test"><meta property="og:image" content="an image">',
            ],
        ];
    }

    /**
     * Test `metaOpenGraph` method
     *
     * @covers ::metaOpenGraph()
     * @param array $data The data for meta
     * @param string $expected The expected meta html
     * @return void
     */
    #[DataProvider('metaOpenGraphProvider')]
    public function testMetaOpenGraph(array $data, string $expected): void
    {
        $actual = $this->Html->metaOpenGraph($data);
        static::assertEquals($expected, $actual);
    }

    /**
     * Data provider for `testMetaTwitter` test case.
     *
     * @return array
     */
    public static function metaTwitterProvider(): array
    {
        return [
            'empty data' => [
                [],
                '',
            ],
            'card + site + creator + title + description + image' => [
                [
                    'card' => 'whatever',
                    'site' => 'example.com',
                    'creator' => 'gustavo',
                    'title' => 'dummy',
                    'description' => 'a dummy data for test',
                    'image' => 'an image',
                ],
                '<meta property="twitter:card" content="whatever"><meta property="twitter:site" content="example.com"><meta property="twitter:creator" content="gustavo"><meta property="twitter:title" content="dummy"><meta property="twitter:description" content="a dummy data for test"><meta property="twitter:image" content="an image">',
            ],
        ];
    }

    /**
     * Test `metaTwitter` method
     *
     * @covers ::metaTwitter()
     * @param array $data The data for meta
     * @param string $expected The expected meta html
     * @return void
     */
    #[DataProvider('metaTwitterProvider')]
    public function testMetaTwitter(array $data, string $expected): void
    {
        $actual = $this->Html->metaTwitter($data);
        static::assertEquals($expected, $actual);
    }

    /**
     * Data provider for `testGetMeta` test case.
     *
     * @return array
     */
    public static function getMetaProvider(): array
    {
        return [
            // string
            '(string) empty data default null' => [
                [], // config
                [], // data
                'something', // field
                null, // default val
                '', // expected
            ],
            'description from config' => [
                ['meta' => ['description' => 'whatever']], // config
                [], // data
                'description', // field
                null, // default val
                'whatever', // expected
            ],
            'description from data' => [
                ['meta' => ['description' => 'whatever']], // config
                ['description' => 'whatever from data'], // data
                'description', // field
                null, // default val
                'whatever from data', // expected
            ],
            // array
            '(array) empty data default null' => [
                [], // config
                [], // data
                'something', // field
                null, // default val
                null, // expected
            ],
            'project from config' => [
                ['meta' => ['project' => ['name' => 'gustavo', 'version' => '3.0']]], // config
                [], // data
                'project', // field
                null, // default val
                ['name' => 'gustavo', 'version' => '3.0'], // expected
            ],
            'project from data' => [
                ['meta' => ['project' => ['name' => 'gustavo', 'version' => '3.0']]], // config
                ['project' => ['name' => 'gustavo', 'version' => '4.0']], // data
                'project', // field
                null, // default val
                ['name' => 'gustavo', 'version' => '4.0'], // expected
            ],
        ];
    }

    /**
     * Test `getMeta` method
     *
     * @covers ::getMeta()
     * @covers ::initialize()
     * @param array $config The configuration
     * @param array $data The data
     * @param array $string The field for data
     * @param array|string|null $defaultVal The default val
     * @param string|array|null $expected The expected meta
     * @return void
     */
    #[DataProvider('getMetaProvider')]
    public function testGetMeta(array $config, array $data, string $field, $defaultVal = null, $expected = null): void
    {
        $this->Html = new HtmlHelper(new View(), $config);
        $actual = $this->Html->getMeta($data, $field, $defaultVal);
        static::assertEquals($expected, $actual);
    }

    /**
     * Data provider for `testScript` test case.
     *
     * @return array
     */
    public static function scriptProvider(): array
    {
        return [
            'simple' => [
                ['script' => ['src' => '/script-622a2cc4f5.js']],
                'script',
                new RevManifestStrategy(),
            ],
            'not found' => [
                ['script' => ['src' => '/functions.js']],
                'functions.js',
                new RevManifestStrategy(),
            ],
            'multi' => [
                [
                    ['script' => ['src' => '/script-622a2cc4f5.js']],
                    '/script',
                    ['script' => ['src' => '/page-1x4f92530c.js']],
                    '/script',
                ],
                ['script', 'page'],
                new RevManifestStrategy(),
            ],
            'entrypoint app' => [
                [
                    ['script' => ['src' => '/build/runtime.f011bcb1.js']],
                    '/script',
                    ['script' => ['src' => '/build/0.54651780.js']],
                    '/script',
                    ['script' => ['src' => '/build/app.82269f26.js']],
                    '/script',
                ],
                'app',
                new EntrypointsStrategy(['manifestPath' => WWW_ROOT . 'entrypoints.json']),
            ],
        ];
    }

    /**
     * Test `script` method
     *
     * @param array $expected The expected result
     * @param string|string[] $name The asset name
     * @param \BEdita\WebTools\Utility\Asset\AssetStrategyInterface $strategy The asset strategy to adopt
     * @return void
     * @covers ::script()
     */
    #[DataProvider('scriptProvider')]
    public function testScript($expected, $name, AssetStrategyInterface $strategy): void
    {
        AssetsRevisions::setStrategy($strategy);
        $result = $this->Html->script($name);
        $this->assertHtml($expected, $result);
    }

    /**
     * Data provider for `testCss` test case.
     *
     * @return array
     */
    public static function cssProvider(): array
    {
        return [
            'simple' => [
                [
                    'link' => [
                        'rel' => 'stylesheet',
                        'href' => '/style-b7c54b4c5a.css',
                    ],
                ],
                'style',
                new RevManifestStrategy(),
            ],
            'not found' => [
                [
                    'link' => [
                        'rel' => 'stylesheet',
                        'href' => '/home.css',
                    ],
                ],
                'home.css',
                new RevManifestStrategy(),
            ],
            'multi' => [
                [
                    [
                        'link' => [
                            'rel' => 'stylesheet',
                            'href' => '/style-b7c54b4c5a.css',
                        ],
                    ],
                    [
                        'link' => [
                            'rel' => 'stylesheet',
                            'href' => '/page.css',
                        ],
                    ],
                ],
                ['style', 'page'],
                new RevManifestStrategy(),
            ],
            'entrypoint style' => [
                [
                    'link' => [
                        'rel' => 'stylesheet',
                        'href' => '/build/style.12c5249c.css',
                    ],
                ],
                'style',
                new EntrypointsStrategy(['manifestPath' => WWW_ROOT . 'entrypoints.json']),
            ],
        ];
    }

    /**
     * Test `css` method
     *
     * @param array $expected The expected result
     * @param string|string[] $name The asset name
     * @param \BEdita\WebTools\Utility\Asset\AssetStrategyInterface $strategy The asset strategy to adopt
     * @return void
     * @covers ::css()
     */
    #[DataProvider('cssProvider')]
    public function testCss($expected, $name, AssetStrategyInterface $strategy): void
    {
        AssetsRevisions::setStrategy($strategy);
        $result = $this->Html->css($name);
        $this->assertHtml($expected, $result);
    }

    /**
     * Data provider for `testAssets` test case.
     *
     * @return array
     */
    public static function assetsProvider(): array
    {
        return [
            'css found and js fallback' => [
                [
                    'link' => [
                        'rel' => 'stylesheet',
                        'href' => '/style-b7c54b4c5a.css',
                    ],
                    'script' => [
                        'src' => '/style.js',
                    ],
                ],
                'style',
                new RevManifestStrategy(),
            ],
            'entrypoint style' => [
                [
                    'link' => [
                        'rel' => 'stylesheet',
                        'href' => '/build/style.12c5249c.css',
                    ],
                    'script' => [
                        'src' => '/build/runtime.f011bcb1.js',
                    ],
                ],
                'style',
                new EntrypointsStrategy(['manifestPath' => WWW_ROOT . 'entrypoints.json']),
            ],
        ];
    }

    /**
     * Test `asset` method.
     *
     * @param array $expected The expected result
     * @param string|string[] $name The asset name
     * @param \BEdita\WebTools\Utility\Asset\AssetStrategyInterface $strategy The asset strategy to adopt
     * @return void
     * @covers ::assets()
     */
    #[DataProvider('assetsProvider')]
    public function testAssets($expected, $name, AssetStrategyInterface $strategy): void
    {
        AssetsRevisions::setStrategy($strategy);
        $result = $this->Html->assets($name);
        $this->assertHtml($expected, $result);
    }
}
