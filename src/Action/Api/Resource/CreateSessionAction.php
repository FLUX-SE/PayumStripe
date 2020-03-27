<?php

declare(strict_types=1);

namespace Prometee\PayumStripeCheckoutSession\Action\Api\Resource;

use Prometee\PayumStripeCheckoutSession\Request\Api\Resource\CreateSession;
use Stripe\Checkout\Session;

final class CreateSessionAction extends AbstractCreateAction
{
    /**
     * {@inheritDoc}
     */
    public function getApiResourceClass(): string
    {
        return Session::class;
    }

    /**
     * {@inheritDoc}
     */
    public function supportAlso($request): bool
    {
        return $request instanceof CreateSession;
    }
}
