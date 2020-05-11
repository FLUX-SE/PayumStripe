<?php

declare(strict_types=1);

namespace Prometee\PayumStripe\Request\Api;

use Payum\Core\Request\Generic;

final class Pay extends Generic
{
    public function __construct($firstModel = null, $currentModel = null)
    {
        parent::__construct($firstModel);

        $this->setModel($currentModel);
    }

    public function setToken($token)
    {
        $this->token = $token;
    }
}
