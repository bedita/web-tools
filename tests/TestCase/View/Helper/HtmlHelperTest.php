<?php
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

use BEdita\WebTools\View\Helper\HtmlHelper;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;
use Cake\View\View;

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
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        // create helper
        $this->Html = new HtmlHelper(new View());
    }

    /**
     * {@inheritDoc}
     */
    public function tearDown(): void
    {
        unset($this->Html);

        parent::tearDown();
    }

    /**
     * Data provider for `testTitle` test case.
     *
     * @return array
     */
    public function titleProvider(): array
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
            'title from controller, action' => [
                'my_controller',
                'my_action',
                null,
                'My Controller - My Action',
            ],
            'title from controller, action' => [
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
     * @dataProvider titleProvider()
     * @covers ::title()
     * @param string|null $controllerName The controller name
     * @param string|null $actionName The action name
     * @param string|null $viewVarTitle The title
     * @param string $expected The expected title
     * @return void
     */
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
    public function metaDescriptionProvider(): array
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
                '<meta name="description" content="dummy"/>',
            ],
            'description with special chars and tags' => [
                'dummy <> & dummy',
                '<meta name="description" content="dummy  &amp;amp; dummy"/>',
            ],
        ];
    }

    /**
     * Test `metaDescription` method
     *
     * @dataProvider metaDescriptionProvider()
     * @covers ::metaDescription()
     * @param string|null $description The description
     * @param string $expected The expected meta description
     * @return void
     */
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
    public function metaAuthorProvider(): array
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
                '<meta name="author" content="dummy"/>',
            ],
            'creator with special chars and tags' => [
                'dummy <> & dummy',
                '<meta name="author" content="dummy &amp;lt;&amp;gt; &amp;amp; dummy"/>',
            ],
        ];
    }

    /**
     * Test `metaAuthor` method
     *
     * @dataProvider metaAuthorProvider()
     * @covers ::metaAuthor()
     * @param string|null $creator The content creator
     * @param string $expected The expected meta content author
     * @return void
     */
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
    public function metaCssProvider(): array
    {
        return [
            'empty docType' => [
                '',
                '<meta http-equiv="Content-Style-Type" content="text/css"/>',
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
     * @dataProvider metaCssProvider()
     * @covers ::metaCss()
     * @param string $docType The doc type
     * @param string $expected The expected meta content author
     * @return void
     */
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
    public function metaGeneratorProvider(): array
    {
        return [
            'empty project and version' => [
                [],
                '',
            ],
            'empty project and version' => [
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
                '<meta name="generator" content="Dummy"/>',
            ],
            'project and version' => [
                [
                    'name' => 'Dummy',
                    'version' => '1.0',
                ],
                '<meta name="generator" content="Dummy 1.0"/>',
            ],
        ];
    }

    /**
     * Test `metaGenerator` method
     *
     * @dataProvider metaGeneratorProvider()
     * @covers ::metaGenerator()
     * @param array $project The project data ('name', 'version')
     * @param string $expected The expected meta content author
     * @return void
     */
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
    public function metaAllProvider(): array
    {
        return [
            'empty data' => [
                [],
                '<meta http-equiv="Content-Style-Type" content="text/css"/>',
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
                '<meta name="description" content="dummy description"/><meta name="author" content="gustavo"/><meta http-equiv="Content-Style-Type" content="text/css"/><meta name="generator" content="my dummy project 2.0"/>',
            ],
        ];
    }

    /**
     * Test `metaAll` method
     *
     * @dataProvider metaAllProvider()
     * @covers ::metaAll()
     * @param array $data The data for meta
     * @param string $expected The expected meta html
     * @return void
     */
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
    public function metaOpenGraphProvider(): array
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
                '<meta property="og:url" content="https://example.com"/><meta property="og:title" content="dummy"/><meta property="og:description" content="a dummy data for test"/><meta property="og:image" content="an image"/>',
            ],
        ];
    }

    /**
     * Test `metaOpenGraph` method
     *
     * @dataProvider metaOpenGraphProvider()
     * @covers ::metaOpenGraph()
     * @param array $data The data for meta
     * @param string $expected The expected meta html
     * @return void
     */
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
    public function metaTwitterProvider(): array
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
                '<meta property="twitter:card" content="whatever"/><meta property="twitter:site" content="example.com"/><meta property="twitter:creator" content="gustavo"/><meta property="twitter:title" content="dummy"/><meta property="twitter:description" content="a dummy data for test"/><meta property="twitter:image" content="an image"/>',
            ],
        ];
    }

    /**
     * Test `metaTwitter` method
     *
     * @dataProvider metaTwitterProvider()
     * @covers ::metaTwitter()
     * @param array $data The data for meta
     * @param string $expected The expected meta html
     * @return void
     */
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
    public function getMetaProvider(): array
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
     * @dataProvider getMetaProvider()
     * @covers ::getMeta()
     * @covers ::initialize()
     * @param array $config The configuration
     * @param array $data The data
     * @param array $string The field for data
     * @param array|string|null $defaultVal The default val
     * @param string|array|null $expected The expected meta
     * @return void
     */
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
    public function scriptProvider(): array
    {
        return [
            'simple' => [
                '<script src="/script-622a2cc4f5.js"></script>',
                'script',
            ],
            'not found' => [
                '<script src="/functions.js"></script>',
                'functions.js',
            ],
            'multi' => [
                '<script src="/script-622a2cc4f5.js"></script>'.
                "\n\t" .
                '<script src="/page-1x4f92530c.js"></script>',
                ['script', 'page'],
            ],
        ];
    }

    /**
     * Test `script` method
     *
     * @dataProvider scriptProvider()
     * @covers ::script()
     *
     * @param string|string[] $expected The expected result
     * @param string|string[] $name The asset name
     * @return void
     */
    public function testScript($expected, $name): void
    {
        $result = $this->Html->script($name);
        static::assertEquals($expected, trim($result));
    }

    /**
     * Data provider for `testCss` test case.
     *
     * @return array
     */
    public function cssProvider(): array
    {
        return [
            'simple' => [
                '<link rel="stylesheet" href="/style-b7c54b4c5a.css"/>',
                'style',
            ],
            'not found' => [
                '<link rel="stylesheet" href="/home.css"/>',
                'home.css',
            ],
            'multi' => [
                '<link rel="stylesheet" href="/style-b7c54b4c5a.css"/>'.
                "\n\t" .
                '<link rel="stylesheet" href="/page.css"/>',
                ['style', 'page'],
            ],
        ];
    }
    /**
     * Test `script` method
     *
     * @dataProvider cssProvider()
     * @covers ::css()
     *
     * @param string|string[] $expected The expected result
     * @param string|string[] $name The asset name
     * @return void
     */
    public function testCss($expected, $name): void
    {
        $result = $this->Html->css($name);
        static::assertEquals($expected, trim($result));
    }
}
