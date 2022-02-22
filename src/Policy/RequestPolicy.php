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
namespace BEdita\WebTools\Policy;

use Authorization\IdentityInterface;
use Authorization\Policy\Exception\MissingMethodException;
use Authorization\Policy\RequestPolicyInterface;
use Authorization\Policy\Result;
use Cake\Core\App;
use Cake\Core\InstanceConfigTrait;
use Cake\Http\ServerRequest;
use Cake\Utility\Hash;

/**
 * RequestPolicy class.
 * Given a request and an identity applies the corresponding rule for controller and action.
 */
class RequestPolicy implements RequestPolicyInterface
{
    use InstanceConfigTrait;

    /**
     * Default configuration.
     *
     * - `rules` an array of rules to apply.
     *    The keys of the array are the Controller names. Values can be:
     *    - an array of roles (or a role name) to check against
     *    - a class name or instance that implements `\Authorization\Policy\RequestPolicyInterface`
     *    - a callable item
     *    - an array with controller actions as keys and values one of the above values
     *
     *    Examples of rules are:
     *    ```
     *    [
     *        // ControllerName => rules
     *        'SingleRole' => 'admin', // check identity against `admin` role
     *        'ArrayRole' => ['editor', 'manager'], // check identity against one of these roles
     *        'FullQualifiedClassName' => 'PolicyClass::class', // it needs to implements RequestPolicyInterface
     *        'Custom' => 'CustomAppPolicy',  // search for `\App\Policy\CustomAppPolicy`. It needs to implements RequestPolicyInterface
     *        'Gustavo' => function ($identity, $request) { // it applies the policy callback to all actions of GustavoController
     *            // here the policy
     *        },
     *        'Supporto' => new \Super\Custom\Policy(), // the class must be an instance of RequestPolicyInterface
     *                                                  // or must implement __invoke(?IdentityInterface $identity, ServerRequest $request) magic method
     *        'Mixed' => [
     *            'index' => 'PolicyClass::class', // it applies rule only to MixedController::index() action
     *            'view' => ['editor', 'manager'], // it applies roles rule only to MixedController::view() action,
     *            'here' => function ($identity, $request) {
     *                // here the policy
     *            },
     *            '*' => ['admin'], // fallback rule for all other controller actions
     *         ],
     *     ]
     *     ```
     * - `ruleRequired` set true to forbidden access when missing rule for controller.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'rules' => [],
        'ruleRequired' => false,
    ];

    /**
     * Constructor.
     * Setup policy configuration.
     *
     * @param array $config The configuration
     */
    public function __construct(array $config = [])
    {
        $this->setConfig($config);
    }

    /**
     * Method to check if the request can be accessed.
     *
     * @param \Authorization\IdentityInterface|null $identity Identity
     * @param \Cake\Http\ServerRequest $request Server Request
     * @return bool|\Authorization\Policy\ResultInterface
     */
    public function canAccess(?IdentityInterface $identity, ServerRequest $request)
    {
        $rule = $this->getRule($request);
        if (empty($rule)) {
            return $this->missingRuleResult();
        }

        if ($identity === null) {
            return new Result(false, 'missing identity');
        }

        if ($rule instanceof RequestPolicyInterface) {
            return $rule->canAccess($identity, $request);
        }

        if (is_callable($rule)) {
            return $rule($identity, $request);
        }

        if (is_array($rule)) {
            return $this->applyRolesPolicy($identity, $rule);
        }

        if (!is_string($rule)) {
            throw new \LogicException(sprintf(
                'Invalid rule for %s::%s() in RequestPolicy',
                $request->getParam('controller'),
                $request->getParam('action')
            ));
        }

        $policyRuleClass = App::className($rule, 'Policy');
        if ($policyRuleClass === null) {
            return $this->applyRolesPolicy($identity, [$rule]);
        }

        $policyRule = new $policyRuleClass();
        if (!$policyRule instanceof RequestPolicyInterface) {
            throw new MissingMethodException(['canAccess', 'access', get_class($policyRule)]);
        }

        return $policyRule->canAccess($identity, $request);
    }

    /**
     * Build missing rule result.
     *
     * @return \Authorization\Policy\Result
     */
    protected function missingRuleResult(): Result
    {
        if ($this->getConfig('ruleRequired') === true) {
            return new Result(false, 'required rule is missing');
        }

        return new Result(true);
    }

    /**
     * Apply a simple role policy.
     * If `$identity` belongs to one of `$roles` than it can access.
     *
     * @param \Authorization\IdentityInterface $identity Identity
     * @param array $roles The roles to check against
     * @return \Authorization\Policy\Result
     */
    protected function applyRolesPolicy(IdentityInterface $identity, array $roles): Result
    {
        $identityRoles = (array)Hash::get((array)$identity->getOriginalData(), 'roles');

        if (empty(array_intersect($identityRoles, $roles))) {
            return new Result(false, 'request forbidden for identity\'s roles');
        }

        return new Result(true);
    }

    /**
     * Get the rule to apply.
     *
     * @param \Cake\Http\ServerRequest $request Server Request
     * @return mixed
     */
    protected function getRule(ServerRequest $request)
    {
        $controller = $request->getParam('controller');
        $rule = $this->getConfig(sprintf('rules.%s', $controller));
        if ($rule === null) {
            return false; // no rule was set
        }

        if (is_string($rule) || is_callable($rule) || $rule instanceof RequestPolicyInterface) {
            return $rule;
        }

        if (!is_array($rule)) {
            throw new \LogicException(sprintf('Invalid Rule for %s in RequestPolicy', $controller));
        }

        $action = $request->getParam('action');
        $defaultRule = Hash::get($rule, '*');

        return Hash::get($rule, $action, $defaultRule);
    }
}
