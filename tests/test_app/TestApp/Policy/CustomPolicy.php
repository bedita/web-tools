<?php
declare(strict_types=1);

namespace TestApp\Policy;

use Authorization\IdentityInterface;
use Authorization\Policy\RequestPolicyInterface;
use Authorization\Policy\ResultInterface;
use Cake\Http\ServerRequest;

class CustomPolicy implements RequestPolicyInterface
{
    public function canAccess(?IdentityInterface $identity, ServerRequest $request): ResultInterface|bool
    {
        return true;
    }
}
