<?php

declare(strict_types=1);

namespace Prometee\PayumStripeCheckoutSession\Request\Api;

use Payum\Core\Request\Convert;
use Payum\Core\Security\TokenInterface;
use Prometee\PayumStripeCheckoutSession\Wrapper\EventWrapper;
use Stripe\Event;


/**
 * @method EventWrapper|null getResult()
 */
final class ResolveWebhookEvent extends Convert
{
    /**
     * @param TokenInterface|null $token
     */
    public function __construct(TokenInterface $token = null)
    {
        parent::__construct(null, Event::class, $token);
    }
}
