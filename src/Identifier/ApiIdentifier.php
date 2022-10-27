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

namespace BEdita\WebTools\Identifier;

use Authentication\Identifier\AbstractIdentifier;
use BEdita\SDK\BEditaClientException;
use BEdita\WebTools\ApiClientProvider;
use Cake\Log\LogTrait;
use Cake\Utility\Hash;
use Psr\Log\LogLevel;

/**
 * Identifier for authentication using BEdita 4 API /auth endpoint.
 */
class ApiIdentifier extends AbstractIdentifier
{
    use LogTrait;

    /**
     * Default configuration.
     *
     * - fields: the fields used for identify user
     *
     * @var array
     */
    protected $_defaultConfig = [
        'fields' => [
            self::CREDENTIAL_USERNAME => 'username',
            self::CREDENTIAL_PASSWORD => 'password',
        ],
    ];

    /**
     * @inheritDoc
     */
    public function identify(array $data)
    {
        $usernameField = $this->getConfig('fields.' . self::CREDENTIAL_USERNAME);
        $passwordField = $this->getConfig('fields.' . self::CREDENTIAL_PASSWORD);
        if (!isset($data[$usernameField]) || !isset($data[$passwordField])) {
            return null;
        }

        /** @var \BEdita\SDK\BEditaClient $apiClient */
        $apiClient = ApiClientProvider::getApiClient();
        try {
            $result = $apiClient->authenticate(
                $data[$usernameField],
                $data[$passwordField]
            );
            if (empty($result['meta'])) {
                $this->setError('Invalid username or password');

                return null;
            }
            $tokens = $result['meta'];
            $result = $apiClient->get('/auth/user', null, ['Authorization' => sprintf('Bearer %s', $tokens['jwt'])]);
        } catch (BEditaClientException $e) {
            $this->log('Login failed - ' . $e->getMessage(), LogLevel::INFO);
            $this->setError($e->getMessage());

            return null;
        }

        $roles = Hash::extract($result, 'included.{n}.attributes.name');

        return $result['data'] + compact('tokens') + compact('roles');
    }

    /**
     * Add error message to `self::_errors`
     *
     * @param string $message The error message
     * @return void
     */
    protected function setError($message)
    {
        $this->_errors[] = $message;
    }
}
