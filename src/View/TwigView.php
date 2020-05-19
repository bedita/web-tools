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
namespace BEdita\WebTools\View;

use BEdita\WebTools\View\Twig\BeditaTwigExtension;
use Cake\Core\Configure;
use Cake\TwigView\View\TwigView as BaseTwigView;

/**
 * View class that uses TwigView and adds Twig extensions
 */
class TwigView extends BaseTwigView
{
    /**
     * {@inheritDoc}
     */
    public function initialize(): void
    {
        $environment = (array)Configure::read('Twig.environment', []) + ['strict_variables' => false];
        $this->setConfig('environment', $environment);

        parent::initialize();
    }

    /**
     * {@inheritDoc}
     */
    public function initializeExtensions(): void
    {
        parent::initializeExtensions();

        $this->getTwig()
            ->addExtension(new BeditaTwigExtension());
    }
}
