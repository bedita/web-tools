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
    public function setUp() : void
    {
        parent::setUp();

        // create helper
        $this->Html = new HtmlHelper(new View());
    }

    /**
     * {@inheritDoc}
     */
    public function tearDown() : void
    {
        unset($this->Html);

        parent::tearDown();
    }

    /**
     * Data provider for `testTitle` test case.
     *
     * @return array
     */
    public function titleProvider() : array
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
                'My Controller',
                null,
                null,
                'My Controller',
            ],
            'title from controller, action' => [
                'My Controller',
                'My Action',
                null,
                'My Controller - My Action',
            ],
            'title from controller, action' => [
                'My Controller',
                'My Action',
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
    public function testTitle(?string $controllerName, ?string $actionName, ?string $viewVarTitle, string $expected) : void
    {
        $this->Html->getView()->request->params['controller'] = $controllerName;
        $this->Html->getView()->request->params['action'] = $actionName;
        $this->Html->getView()->viewVars['_title'] = $viewVarTitle;
        $actual = $this->Html->title();
        static::assertEquals($expected, $actual);
    }
}
