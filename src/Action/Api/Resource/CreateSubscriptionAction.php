<?php

declare(strict_types=1);

namespace Prometee\PayumStripeCheckoutSession\Action\Api\Resource;

use Prometee\PayumStripeCheckoutSession\Request\Api\Resource\CreateSubscription;
use Stripe\Subscription;

class CreateSubscriptionAction extends AbstractCreateAction
{
    /** @var string|Subscription */
    protected $apiResourceClass = Subscription::class;

    /**
     * {@inheritDoc}
     */
    public function supportAlso($request): bool
    {
        return $request instanceof CreateSubscription;
    }
}
