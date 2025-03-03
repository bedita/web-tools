<?php
declare(strict_types=1);

/**
 * BEdita, API-first content management framework
 * Copyright 2022 Atlas Srl, Chialab Srl
 *
 * This file is part of BEdita: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * See LICENSE.LGPL or <http://gnu.org/licenses/lgpl-3.0.html> for more details.
 */
namespace BEdita\WebTools\View\Helper;

use Authentication\View\Helper\IdentityHelper as AuthenticationIdentityHelper;
use BadMethodCallException;

/**
 * Extends IdentityHelper allowing to delegate methods to the identity.
 */
class IdentityHelper extends AuthenticationIdentityHelper
{
    /**
     * Configuration options
     *
     * - `identityAttribute` - The request attribute which holds the identity.
     * - `delagateMethods` - Methods delegated to identity.
     *
     * @var array
     */
    protected array $_defaultConfig = [
        'identityAttribute' => 'identity',
        'delegateMethods' => [
            'hasRole',
        ],
    ];

    /**
     * Delegate methods to identity.
     *
     * @param string $method The method being invoked.
     * @param array $args The arguments for the method.
     * @return mixed
     */
    public function __call(string $method, array $args): mixed
    {
        if (!in_array($method, (array)$this->getConfig('delegateMethods'))) {
            throw new BadMethodCallException("Cannot call `{$method}`. Make sure to add it to `delegateMethods`.");
        }

        if (!is_object($this->_identity)) {
            throw new BadMethodCallException("Cannot call `{$method}` on stored identity since it is not an object.");
        }

        $call = [$this->_identity, $method];

        return $call(...$args);
    }
}
