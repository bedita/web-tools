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
namespace BEdita\WebTools\View\Twig;

use Cake\Core\Configure;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * BEdita Twig extension class.
 *
 * Provide BEdita utils to Twig view.
 */
class BeditaTwigExtension extends AbstractExtension
{
    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'bedita';
    }

    /**
     * @inheritDoc
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('config', [Configure::class, 'read']),
            new TwigFunction('write_config', function ($key, $val): void {
                // avoid unwanted return value display in templates
                Configure::write($key, $val);
            }),
        ];
    }

    /**
     * @inheritDoc
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter(
                'shuffle',
                function (array $array) {
                    shuffle($array);

                    return $array;
                }
            ),
            new TwigFilter(
                'ksort',
                function (array $array) {
                    ksort($array);

                    return $array;
                }
            ),
            new TwigFilter(
                'krsort',
                function (array $array) {
                    krsort($array);

                    return $array;
                }
            ),
        ];
    }
}
