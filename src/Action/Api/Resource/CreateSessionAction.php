<?php

declare(strict_types=1);

namespace Prometee\PayumStripeCheckoutSession\Action\Api\Resource;

use Prometee\PayumStripeCheckoutSession\Request\Api\Resource\CreateSession;
use Stripe\Checkout\Session;

final class CreateSessionAction extends AbstractCreateAction
{
    /** @var string|Session */
    protected $apiResourceClass = Session::class;

    /**
     * {@inheritDoc}
     */
    public function supportAlso($request): bool
    {
        return $request instanceof CreateSession;
    }
}
