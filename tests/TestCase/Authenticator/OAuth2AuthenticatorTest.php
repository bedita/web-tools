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

use Authentication\Authenticator\Result;
use Authentication\Identifier\IdentifierInterface;
use BEdita\WebTools\Authenticator\OAuth2Authenticator;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\ServerRequest;
use Cake\Http\Session;
use Cake\TestSuite\TestCase;
use Cake\Utility\Hash;

/**
 * {@see \BEdita\WebTools\Authenticator\OAuth2Authenticator} Test Case
 *
 * @coversDefaultClass \BEdita\WebTools\Authenticator\OAuth2Authenticator
 */
class OAuth2AuthenticatorTest extends TestCase
{
    /**
     * Data provider for `testAuthenticate` test case.
     *
     * @return array
     */
    public function authenticateProvider(): array
    {
        return [
            'bad provider' => [
                new BadRequestException('Invalid auth provider gustavo'),
                [
                    'url' => '/ext/login/gustavo',
                ],
            ],
            'auth url ok' => [
                [
                    'status' => Result::SUCCESS,
                ],
                [
                    'url' => '/ext/login/gustavo',
                ],
                [
                    'urlResolver' => fn () => '',
                    'providers' => [
                        'gustavo' => [
                            'class' => TestProvider::class,
                            'setup' => [
                                'clientId' => '',
                            ],
                        ],
                    ],
                ],
            ],
            'invalid state' => [
                new BadRequestException('Invalid state'),
                [
                    'url' => '/ext/login/gustavo?code=1&state=1',
                ],
                [
                    'urlResolver' => fn () => '',
                    'providers' => [
                        'gustavo' => [
                            'class' => TestProvider::class,
                            'setup' => [
                                'clientId' => '',
                            ],
                        ],
                    ],
                ],
            ],
            'auth ok' => [
                [
                    'status' => Result::SUCCESS,
                ],
                [
                    'url' => '/ext/login/gustavo?code=1&state=abc&redirect=here',
                    'data' => [
                        'oauth2state' => 'abc',
                    ],
                ],
                [
                    'urlResolver' => fn () => '',
                    'providers' => [
                        'gustavo' => [
                            'class' => TestProvider::class,
                            'setup' => [
                                'clientId' => '',
                            ],
                        ],
                    ],
                ],
            ],
            'auth fail' => [
                [
                    'status' => Result::FAILURE_IDENTITY_NOT_FOUND,
                ],
                [
                    'url' => '/ext/login/gustavo?code=1&state=abc',
                    'data' => [
                        'oauth2state' => 'abc',
                    ],
                ],
                [
                    'urlResolver' => function () {
                        return '';
                    },
                    'providers' => [
                        'gustavo' => [
                            'class' => TestProvider::class,
                            'setup' => [
                                'clientId' => '',
                            ],
                        ],
                    ],
                ],
                [],
            ],
        ];
    }

    /**
     * Test `authenticate` method
     *
     * @param array|\Exception $expected EXpected result.
     * @param array $config Request configuration.
     * @param array $authConfig Authenticator configuration.
     * @param array $identity Identity data.
     * @return void
     * @dataProvider authenticateProvider
     * @covers ::authenticate()
     * @covers ::providerConnect()
     * @covers ::initProvider()
     * @covers ::redirectUri()
     * @covers ::__construct()
     */
    public function testAuthenticate(
        $expected,
        array $reqConfig,
        array $authConfig = [],
        array $identity = ['id' => 1]
    ): void {
        if ($expected instanceof \Exception) {
            $this->expectException(get_class($expected));
            $this->expectExceptionCode($expected->getCode());
            $this->expectExceptionMessage($expected->getMessage());
        }

        $identifier = new class ($identity) implements IdentifierInterface {
            public function __construct($identity) {
                $this->identity = $identity;
            }

            public function identify(array $credentials)
            {
                return $this->identity;
            }

            public function getErrors(): array
            {
                return [];
            }
        };
        $request = new ServerRequest($reqConfig);
        $session = new Session();
        $session->write(Hash::get($reqConfig, 'data'));
        $request = $request->withAttribute('session', $session);
        $authenticator = new OAuth2Authenticator($identifier, $authConfig);
        $result = $authenticator->authenticate($request);

        static::assertNotNull($result);
        static::assertEquals($expected['status'], $result->getStatus());
    }
}
