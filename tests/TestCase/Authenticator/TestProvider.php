<?php
declare(strict_types=1);

/**
 * BEdita, API-first content management framework
 * Copyright 2022 ChannelWeb Srl, Chialab Srl
 *
 * This file is part of BEdita: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * See LICENSE.LGPL or <http://gnu.org/licenses/lgpl-3.0.html> for more details.
 */

namespace BEdita\WebTools\Test\TestCase\Authenticator;

use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Provider\GenericResourceOwner;
use League\OAuth2\Client\Token\AccessToken;

class TestProvider extends GenericProvider
{
    protected function getRequiredOptions()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getAccessToken($grant, array $options = [])
    {
        return new AccessToken(['access_token' => 'test-token']);
    }

    /**
     * @inheritDoc
     */
    public function getResourceOwner(AccessToken $token)
    {
        return new GenericResourceOwner(['1' => '1'], '1');
    }
}
