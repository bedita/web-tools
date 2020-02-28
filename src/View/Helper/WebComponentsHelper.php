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

namespace BEdita\WebTools\View\Helper;

use Cake\View\Helper\HtmlHelper as CakeHtmlHelper;

/**
 * Helper to handle Web Components initialization with properties.
 */
class WebComponentsHelper extends CakeHtmlHelper
{
    private $ids = [];

    /**
     * Pass properties to an HTMLElement using attributes for plain values and inline scripts for array.
     *
     * @param array $properties A list of properties to set.
     * @return string An attributes string list like `data-wc="0" attr1="value"`.
     */
    public function props(array $properties): string
    {
        if (empty($properties)) {
            return '';
        }

        $id = count($this->ids);
        $this->ids[] = $id;

        $attributes = [];
        $statements = [];
        foreach ($properties as $key => $value) {
            if (is_string($value) || is_numeric($value)) {
                $attributes[] = sprintf('%s="%s"', $key, $value);
            } else {
                $statements[] = sprintf('elem[\'%s\'] = %s;', $key, json_encode($value));
            }
        }

        if (!empty($statements)) {
            $content = sprintf('(function(){var elem = document.querySelector(\'[data-wc="%s"]\');%s}());if(document.currentScript)document.currentScript.parentNode.removeChild(document.currentScript);', $id, join('', $statements));
            $this->scriptBlock($content, [ 'block' => 'scriptsComponents' ]);
        }

        return trim(sprintf('data-wc="%s" %s', $id, join(' ', $attributes)));
    }

    /**
     * Initialize a Custom Element which extends a native node.
     *
     * @param string $tagName The defined Custom Element name to set as `is` attribute.
     * @param array $properties A list of properties to set.
     * @param string $scriptPath The path of the definition script to import.
     * @return string An attributes string list like `is="my-element" data-wc="0"`.
     */
    public function is(string $tagName, array $properties = [], string $scriptPath = ''): string
    {
        if (!empty($scriptPath)) {
            $this->script($scriptPath, [ 'block' => 'scriptsComponents' ]);
        }

        return trim(sprintf('is="%s" %s', $tagName, $this->props($properties)));
    }

    /**
     * Initialize a Custom Element.
     *
     * @param string $tagName The defined Custom Element name to use as tag name.
     * @param array $properties A list of properties to set.
     * @param string $scriptPath The path of the definition script to import.
     * @return string An HTML node string like `<my-element data-wc="0"></my-element>`.
     */
    public function element(string $tagName, array $properties = [], $scriptPath = ''): string
    {
        if (!empty($scriptPath)) {
            $this->script($scriptPath, [ 'block' => 'scriptsComponents' ]);
        }

        return trim(sprintf('<%s %s></%s>', $tagName, $this->props($properties), $tagName));
    }
}
