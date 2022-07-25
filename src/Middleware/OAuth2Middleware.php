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
namespace BEdita\WebTools\Middleware;

use Authentication\Authenticator\Result;
use BEdita\WebTools\Authenticator\OAuth2Authenticator;
use Cake\Utility\Hash;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Middleware to handle OAuth2 flow.
 */
class OAuth2Middleware implements MiddlewareInterface
{
    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $result = $request->getAttribute('authenticationResult');
        if (empty($result) || !$result instanceof Result || !is_array($result->getData())) {
            return $handler->handle($request);
        }

        $authUrl = Hash::get($result->getData(), OAuth2Authenticator::AUTH_URL_KEY);
        if (empty($authUrl)) {
            return $handler->handle($request);
        }

        return new RedirectResponse($authUrl);
    }
}
