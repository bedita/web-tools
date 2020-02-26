<?php
declare(strict_types=1);

/**
 * BEdita, API-first content management framework
 * Copyright 2019 ChannelWeb Srl, Chialab Srl
 *
 * This file is part of BEdita: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * See LICENSE.LGPL or <http://gnu.org/licenses/lgpl-3.0.html> for more details.
 */

namespace BEdita\WebTools\View\Helper;

use Cake\View\Helper\HtmlHelper;

/**
 * Helper to handle Web Components initialization with properties.
 */
class WebComponentsHelper extends HtmlHelper
{

    /**
     * Pass properties to an HTMLElement using attributes with JSON values.
     *
     * @param array $properties A list of properties to set.
     * @return string An attributes string list like `attr1="value1" attr2="value2"`.
     */
    public function props(array $properties): string
    {
        if (empty($properties)) {
            return '';
        }
        $attributes = [];
        foreach ($properties as $key => $value) {
            $attributes[] = sprintf('%s="%s"', $key, htmlspecialchars(json_encode($value), ENT_QUOTES, 'UTF-8'));
        }

        return join(' ', $attributes);
    }

    /**
     * Initialize a Custom Element which extends a native node.
     *
     * @param string $tagName The defined Custom Element name to set as `is` attribute.
     * @param array $properties A list of properties to set.
     * @param string $scriptPath The path of the definition script to import.
     * @return string An attributes string list like `is="my-element" attr1="value1" attr2="value2"`.
     */
    public function is(string $tagName, array $properties = array(), string $scriptPath = ''): string
    {
        if (!empty($scriptPath)) {
            $this->script($scriptPath, [ 'block' => 'scriptsComponents' ]);
        }
        return sprintf('is="%s" %s', $tagName, $this->props($properties));
    }

    /**
     * Initialize a Custom Element.
     *
     * @param string $tagName The defined Custom Element name to use as tag name.
     * @param array $properties A list of properties to set.
     * @param string $scriptPath The path of the definition script to import.
     * @return string An HTML node string like `<my-element attr1="value1"></my-element>`.
     */
    public function element(string $tagName, array $properties = array(), $scriptPath = ''): string
    {
        if (!empty($scriptPath)) {
            $this->script($scriptPath, [ 'block' => 'scriptsComponents' ]);
        }
        return sprintf('<%s %s></%s>', $tagName, $this->props($properties), $tagName);
    }
}
