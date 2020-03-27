<?php

declare(strict_types=1);

namespace Prometee\PayumStripeCheckoutSession\Request;

use Payum\Core\Request\Generic;
use Payum\Core\Security\TokenInterface;

class DeleteWebhookToken extends Generic
{
    /**
     * @param TokenInterface $token
     */
    public function __construct(TokenInterface $token)
    {
        parent::__construct($token);
    }
}
