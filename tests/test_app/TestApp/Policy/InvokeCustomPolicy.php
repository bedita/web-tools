<?php
declare(strict_types=1);

namespace TestApp\Policy;

use Authorization\IdentityInterface;
use Cake\Http\ServerRequest;

class InvokeCustomPolicy
{
    public function __invoke(?IdentityInterface $identity, ServerRequest $request)
    {
        return true;
    }
}
