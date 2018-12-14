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

namespace BEdita\WebTools\View\Helper;

use Cake\Utility\Inflector;
use Cake\View\Helper\HtmlHelper as CakeHtmlHelper;

/**
 * Html helper.
 * It extends {@see \Cake\View\Helper\HtmlHelper} Cake Html Helper
 */
class HtmlHelper extends CakeHtmlHelper
{
    /**
     * Title for template pages
     * If `_title` view var is set, return it
     * Otherwise return controller name (and action name if set)
     *
     * @return string
     */
    public function title() : string
    {
        if (isset($this->getView()->viewVars['_title'])) {
            return $this->getView()->viewVars['_title'];
        }
        $title = Inflector::humanize($this->getView()->request->getParam('controller', ''));
        $suffix = Inflector::humanize($this->getView()->request->getParam('action', ''));
        if (empty($title)) {
            $title = $suffix;
        } elseif (!empty($suffix)) {
            $title .= sprintf(' - %s', $suffix);
        }

        return $title;
    }
}
