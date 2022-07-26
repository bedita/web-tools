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
namespace BEdita\WebTools\Identifier;

use ArrayObject;
use Authentication\Identifier\AbstractIdentifier;
use BEdita\SDK\BEditaClientException;
use BEdita\WebTools\ApiClientProvider;
use Cake\Log\LogTrait;
use Cake\Utility\Hash;

/**
 * Identifies authentication credentials through an OAuth2 external provider.
 */
class OAuth2Identifier extends AbstractIdentifier
{
    use LogTrait;

    /**
     * Configuration options
     *
     * - `fields` - Fields used in `/auth` endpoint using and external auth provider.
     * - `autoSignup` - flag indicating whether `/signup` should be invoked automatically in case of authentication failure.
     * - `signupRoles` - array of roles to use in `/signup` if `autoSignup` is set to `true`.
     * - `providers` - configured OAuth2 providers, see https://github.com/bedita/web-tools/wiki/OAuth2-providers-configurations
     *
     * @var array
     */
    protected $_defaultConfig = [
        'fields' => [
            'auth_provider' => 'auth_provider',
            'provider_username' => 'provider_username',
            'access_token' => 'access_token',
            'provider_userdata' => 'provider_userdata',
        ],
        'autoSignup' => false,
        'signupRoles' => [],
        'providers' => [], // configured OAuth2 providers
    ];

    /**
     * @inheritDoc
     */
    public function identify(array $credentials)
    {
        try {
            $result = $this->externalAuth($credentials);
        } catch (BEditaClientException $ex) {
            $this->log($ex->getMessage(), 'debug');

            if (!$this->getConfig('autoSignup') || $ex->getCode() !== 401) {
                return null;
            }

            return $this->signup($credentials);
        }

        return $result;
    }

    /**
     * Perform external login via `/auth`.
     *
     * @param array $credentials Identifier credentials
     * @return \ArrayObject
     */
    protected function externalAuth(array $credentials): ArrayObject
    {
        $apiClient = ApiClientProvider::getApiClient();
        $result = $apiClient->post('/auth', json_encode($credentials), ['Content-Type' => 'application/json']);
        $tokens = $result['meta'];
        $result = $apiClient->get('/auth/user', null, ['Authorization' => sprintf('Bearer %s', $tokens['jwt'])]);

        return new ArrayObject($result['data']
            + compact('tokens')
            + Hash::combine($result, 'included.{n}.attributes.name', 'included.{n}.id', 'included.{n}.type'));
    }

    /**
     * Perform OAuth2 signup and login after signup.
     *
     * @param array $credentials Identifier credentials
     * @return \ArrayObject|null;
     */
    protected function signup(array $credentials): ?ArrayObject
    {
        $data = $this->signupData($credentials);
        try {
            $apiClient = ApiClientProvider::getApiClient();
            $apiClient->setupTokens([]);
            $apiClient->post('/signup', json_encode($data), ['Content-Type' => 'application/json']);
            // login after signup
            $user = $this->externalAuth($credentials);
        } catch (BEditaClientException $ex) {
            $this->log($ex->getMessage(), 'warning');
            $this->log(json_encode($ex->getAttributes()), 'warning');

            return null;
        }

        return $user;
    }

    /**
     * Signup data from OAuth2 provider user data.
     *
     * @param array $credentials Identifier credentials
     * @return array
     */
    protected function signupData(array $credentials): array
    {
        $user = (array)$this->getConfig(sprintf('providers.%s.map', $credentials['auth_provider']));
        foreach ($user as $key => $value) {
            $user[$key] = Hash::get($credentials, sprintf('provider_userdata.%s', $value));
        }
        $roles = (array)$this->getConfig('signupRoles');

        return array_filter($user + $credentials + compact('roles'));
    }
}
