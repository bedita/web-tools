<?php
declare(strict_types=1);

/**
 * BEdita, API-first content management framework
 * Copyright 2022 Atlas Srl, ChannelWeb Srl, Chialab Srl
 *
 * This file is part of BEdita: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * See LICENSE.LGPL or <http://gnu.org/licenses/lgpl-3.0.html> for more details.
 */
namespace BEdita\WebTools;

use Authentication\Identity as AuthenticationIdentity;
use Cake\Utility\Hash;

/**
 * Extends Authorization Identity adding useful methods.
 */
class Identity extends AuthenticationIdentity
{
    /**
     * Return true if `$name` is a role of the identity
     *
     * @param string $name The role name
     * @return bool
     */
    public function hasRole(string $name): bool
    {
        return in_array($name, $this->get('roles'));
    }
}
