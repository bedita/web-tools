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
namespace BEdita\WebTools\Test\TestCase;

use ArrayObject;
use Authorization\AuthorizationService;
use Authorization\IdentityDecorator;
use Authorization\Policy\Exception\MissingMethodException;
use Authorization\Policy\Result;
use BEdita\WebTools\Policy\RequestPolicy;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;
use Exception;
use Laminas\Diactoros\Uri;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use TestApp\Policy\CustomPolicy;
use TestApp\Policy\InvokeCustomPolicy;

/**
 * {@see BEdita\WebTools\Policy\RequestPolicy} Test Case
 *
 * @covers \BEdita\WebTools\Policy\RequestPolicy
 */
class RequestPolicyTest extends TestCase
{
    /**
     * Data provider for testCanAccess()
     *
     * @return array
     */
    public function canAccessProvider(): array
    {
        return [
            'missing rule' => [
                new Result(true),
                (new ServerRequest(['uri' => new Uri('/dashboard')]))
                    ->withParam('controller', 'Dashboard')
                    ->withParam('action', 'index'),
                [],
                [
                    'id' => 1,
                    'roles' => ['admin'],
                ],
            ],
            'ko missing rule but required' => [
                new Result(false, 'required rule is missing'),
                (new ServerRequest(['uri' => new Uri('/dashboard')]))
                    ->withParam('controller', 'Dashboard')
                    ->withParam('action', 'index'),
                [
                    'ruleRequired' => true,
                ],
                [
                    'id' => 1,
                    'roles' => ['admin'],
                ],
            ],
            'ko missing identity' => [
                new Result(false, 'missing identity'),
                (new ServerRequest(['uri' => new Uri('/dashboard')]))
                    ->withParam('controller', 'Dashboard')
                    ->withParam('action', 'index'),
                [
                    'rules' => [
                        'Dashboard' => 'admin',
                    ],
                ],
                null,
            ],
            'role ok' => [
                new Result(true),
                (new ServerRequest(['uri' => new Uri('/dashboard')]))
                    ->withParam('controller', 'Dashboard')
                    ->withParam('action', 'index'),
                [
                    'rules' => [
                        'Dashboard' => 'admin',
                    ],
                ],
                [
                    'id' => 1,
                    'roles' => ['admin'],
                ],
            ],
            'ok for some roles' => [
                new Result(true),
                (new ServerRequest(['uri' => new Uri('/dashboard')]))
                    ->withParam('controller', 'Dashboard')
                    ->withParam('action', 'index'),
                [
                    'rules' => [
                        'Dashboard' => ['admin', 'manager'],
                    ],
                ],
                [
                    'id' => 1,
                    'roles' => ['manager'],
                ],
            ],
            'ok for a specific action' => [
                new Result(true),
                (new ServerRequest(['uri' => new Uri('/dashboard/profile')]))
                    ->withParam('controller', 'Dashboard')
                    ->withParam('action', 'profile'),
                [
                    'rules' => [
                        'Dashboard' => [
                            'index' => 'manager',
                            'profile' => ['admin', 'owner'],
                        ],
                    ],
                ],
                [
                    'id' => 1,
                    'roles' => ['admin'],
                ],
            ],
            'ko for a specific action' => [
                new Result(false, 'request forbidden for identity\'s roles'),
                (new ServerRequest(['uri' => new Uri('/dashboard/profile')]))
                    ->withParam('controller', 'Dashboard')
                    ->withParam('action', 'profile'),
                [
                    'rules' => [
                        'Dashboard' => [
                            'index' => 'manager',
                            'profile' => 'admin',
                        ],
                    ],
                ],
                [
                    'id' => 1,
                    'roles' => ['manager'],
                ],
            ],
            'ok for a fallback action' => [
                new Result(true),
                (new ServerRequest(['uri' => new Uri('/dashboard/profile')]))
                    ->withParam('controller', 'Dashboard')
                    ->withParam('action', 'profile'),
                [
                    'rules' => [
                        'Dashboard' => [
                            'index' => 'manager',
                            '*' => 'admin',
                        ],
                    ],
                ],
                [
                    'id' => 1,
                    'roles' => ['admin'],
                ],
            ],
            'ko with callback' => [
                false,
                (new ServerRequest(['uri' => new Uri('/dashboard/profile')]))
                    ->withParam('controller', 'Dashboard')
                    ->withParam('action', 'profile'),
                [
                    'rules' => [
                        'Dashboard' => [
                            'index' => 'manager',
                            'profile' => function ($identity, $request) {
                                return false;
                            },
                        ],
                    ],
                ],
                [
                    'id' => 1,
                    'roles' => ['admin'],
                ],
            ],
            'ok using custom policy fully qualified name' => [
                true,
                (new ServerRequest(['uri' => new Uri('/dashboard/profile')]))
                    ->withParam('controller', 'Dashboard')
                    ->withParam('action', 'profile'),
                [
                    'rules' => [
                        'Dashboard' => CustomPolicy::class,
                    ],
                ],
                [
                    'id' => 1,
                    'roles' => ['admin'],
                ],
            ],
            'ok using custom policy instance' => [
                true,
                (new ServerRequest(['uri' => new Uri('/dashboard/profile')]))
                    ->withParam('controller', 'Dashboard')
                    ->withParam('action', 'profile'),
                [
                    'rules' => [
                        'Dashboard' => new CustomPolicy(),
                    ],
                ],
                [
                    'id' => 1,
                    'roles' => ['admin'],
                ],
            ],
            'ok using callable custom policy instance' => [
                true,
                (new ServerRequest(['uri' => new Uri('/dashboard/profile')]))
                    ->withParam('controller', 'Dashboard')
                    ->withParam('action', 'profile'),
                [
                    'rules' => [
                        'Dashboard' => new InvokeCustomPolicy(),
                    ],
                ],
                [
                    'id' => 1,
                    'roles' => ['admin'],
                ],
            ],
            'ok using simple class name for custom policy' => [
                true,
                (new ServerRequest(['uri' => new Uri('/dashboard/profile')]))
                    ->withParam('controller', 'Dashboard')
                    ->withParam('action', 'profile'),
                [
                    'rules' => [
                        'Dashboard' => 'CustomPolicy',
                    ],
                ],
                [
                    'id' => 1,
                    'roles' => ['admin'],
                ],
            ],
            'ko using simple class name for custom policy' => [
                new MissingMethodException(['canAccess', 'access', InvokeCustomPolicy::class]),
                (new ServerRequest(['uri' => new Uri('/dashboard/profile')]))
                    ->withParam('controller', 'Dashboard')
                    ->withParam('action', 'profile'),
                [
                    'rules' => [
                        'Dashboard' => 'InvokeCustomPolicy',
                    ],
                ],
                [
                    'id' => 1,
                    'roles' => ['admin'],
                ],
            ],
            'ko invalid policy instance' => [
                new LogicException('Invalid rule for Dashboard::profile() in RequestPolicy'),
                (new ServerRequest(['uri' => new Uri('/dashboard/profile')]))
                    ->withParam('controller', 'Dashboard')
                    ->withParam('action', 'profile'),
                [
                    'rules' => [
                        'Dashboard' => [
                            'profile' => new ArrayObject(),
                        ],
                    ],
                ],
                [
                    'id' => 1,
                    'roles' => ['admin'],
                ],
            ],
            'ko invalid rule' => [
                new LogicException('Invalid Rule for Dashboard in RequestPolicy'),
                (new ServerRequest(['uri' => new Uri('/dashboard/profile')]))
                    ->withParam('controller', 'Dashboard')
                    ->withParam('action', 'profile'),
                [
                    'rules' => [
                        'Dashboard' => new ArrayObject(),
                    ],
                ],
                [
                    'id' => 1,
                    'roles' => ['admin'],
                ],
            ],
        ];
    }

    /**
     * Test that rules work as expected.
     *
     * @param mixed $expected The expected result
     * @param \Cake\Http\ServerRequest $request The request
     * @param array $policyConfig Policy configuration
     * @param array $identityData Identity data
     * @return void
     */
    #[DataProvider('canAccessProvider')]
    public function testCanAccess($expected, ServerRequest $request, array $policyConfig, ?array $identityData): void
    {
        if ($expected instanceof Exception) {
            $this->expectException(get_class($expected));
            $this->expectExceptionMessage($expected->getMessage());
        }

        $authService = $this->createMock(AuthorizationService::class);
        if ($identityData === null) {
            $identity = $identityData;
        } else {
            $identity = new IdentityDecorator($authService, $identityData);
        }

        $policy = new RequestPolicy($policyConfig);

        $actual = $policy->canAccess($identity, $request);

        static::assertEquals($expected, $actual);
        if ($expected instanceof Result) {
            static::assertInstanceOf(Result::class, $actual);
            static::assertEquals($expected->getStatus(), $expected->getStatus());
            static::assertEquals($expected->getReason(), $expected->getReason());
        }
    }
}
