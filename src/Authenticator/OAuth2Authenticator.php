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
namespace BEdita\WebTools\Authenticator;

use Authentication\Authenticator\AbstractAuthenticator;
use Authentication\Authenticator\Result;
use Authentication\Authenticator\ResultInterface;
use Authentication\Identifier\IdentifierInterface;
use Cake\Http\Exception\BadRequestException;
use Cake\Log\LogTrait;
use Cake\Routing\Router;
use Cake\Utility\Hash;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Authenticator class for the OAuth2 flow.
 * Provides a connection to the external OAuth2 provider and use
 * the identifier class to verify the credentials.
 */
class OAuth2Authenticator extends AbstractAuthenticator
{
    use LogTrait;

    /**
     * External Auth provider
     *
     * @var \League\OAuth2\Client\Provider\AbstractProvider
     */
    protected $provider = null;

    /**
     * Authentication URL key
     *
     * @var string
     */
    public const AUTH_URL_KEY = 'authUrl';

    /**
     * @inheritDoc
     */
    protected $_defaultConfig = [
        'sessionKey' => 'oauth2state',
        'redirect' => ['_name' => 'login'], // named route used to redirect
        'providers' => [], // configured OAuth2 providers
        'urlResolver' => null,
    ];

    /**
     * Constructor
     *
     * @param \Authentication\Identifier\IdentifierInterface $identifier Identifier or identifiers collection.
     * @param array $config Configuration settings.
     */
    public function __construct(IdentifierInterface $identifier, array $config = [])
    {
        // Setup default URL resolver
        $this->setConfig('urlResolver', fn ($route) => Router::url($route, true));
        parent::__construct($identifier, $config);
    }

    /**
     * @inheritDoc
     */
    public function authenticate(ServerRequestInterface $request): ResultInterface
    {
        // extract provider from request
        $provider = basename($request->getUri()->getPath());

        $connect = $this->providerConnect($provider, $request);
        if (!empty($connect[static::AUTH_URL_KEY])) {
            return new Result($connect, Result::SUCCESS);
        }

        $usernameField = (string)$this->getConfig(sprintf('providers.%s.map.provider_username', $provider));
        $data = [
            'auth_provider' => $provider,
            'provider_username' => Hash::get($connect, sprintf('user.%s', $usernameField)),
            'access_token' => Hash::get($connect, 'token.access_token'),
            'provider_userdata' => (array)Hash::get($connect, 'user'),
        ];
        $user = $this->_identifier->identify($data);

        if (empty($user)) {
            return new Result(null, Result::FAILURE_IDENTITY_NOT_FOUND, $this->_identifier->getErrors());
        }

        return new Result($user, Result::SUCCESS);
    }

    /**
     * Perform Oauth2 connect action on Auth Provider.
     *
     * @param string $provider Provider name.
     * @param \Psr\Http\Message\ServerRequestInterface $request Request to get authentication information from.
     * @return array;
     * @throws \Cake\Http\Exception\BadRequestException
     */
    protected function providerConnect(string $provider, ServerRequestInterface $request): array
    {
        $this->initProvider($provider, $request);

        $query = $request->getQueryParams();
        $sessionKey = $this->getConfig('sessionKey');
        /** @var \Cake\Http\Session $session */
        $session = $request->getAttribute('session');

        if (!isset($query['code'])) {
            // If we don't have an authorization code then get one
            $options = (array)$this->getConfig(sprintf('providers.%s.options', $provider));
            $authUrl = $this->provider->getAuthorizationUrl($options);
            $session->write($sessionKey, $this->provider->getState());

            return [static::AUTH_URL_KEY => $authUrl];
        }

        // Check given state against previously stored one to mitigate CSRF attack
        if (empty($query['state']) || ($query['state'] !== $session->read($sessionKey))) {
            $session->delete($sessionKey);
            throw new BadRequestException('Invalid state');
        }

        // Try to get an access token (using the authorization code grant)
        /** @var \League\OAuth2\Client\Token\AccessToken $token */
        $token = $this->provider->getAccessToken('authorization_code', ['code' => $query['code']]);
        // We got an access token, let's now get the user's details
        $user = $this->provider->getResourceOwner($token)->toArray();
        $token = $token->jsonSerialize();

        return compact('token', 'user');
    }

    /**
     * Init external auth provider via configuration
     *
     * @param string $provider Provider name.
     * @param \Psr\Http\Message\ServerRequestInterface $request Request to get authentication information from.
     * @return void
     */
    protected function initProvider(string $provider, ServerRequestInterface $request): void
    {
        $providerConf = (array)$this->getConfig(sprintf('providers.%s', $provider));
        if (empty($providerConf['class']) || empty($providerConf['setup'])) {
            throw new BadRequestException('Invalid auth provider ' . $provider);
        }

        $redirectUri = $this->redirectUri($provider, $request);
        $this->log(sprintf('Creating %s provider with redirect url %s', $provider, $redirectUri), 'info');
        $setup = (array)Hash::get($providerConf, 'setup') + compact('redirectUri');

        $class = Hash::get($providerConf, 'class');
        $this->provider = new $class($setup);
    }

    /**
     * Build redirect URL from request and provider information.
     *
     * @param string $provider Provider name.
     * @param \Psr\Http\Message\ServerRequestInterface $request Request to get authentication information from.
     * @return string
     */
    protected function redirectUri(string $provider, ServerRequestInterface $request): string
    {
        $redirectUri = (array)$this->getConfig('redirect') + compact('provider');
        $query = $request->getQueryParams();
        $queryRedirectUrl = Hash::get($query, 'redirect');
        if (!empty($queryRedirectUrl)) {
            $redirectUri['?'] = ['redirect' => $queryRedirectUrl];
        }

        return call_user_func($this->getConfig('urlResolver'), $redirectUri);
    }
}
