<?php

declare(strict_types=1);

namespace Prometee\PayumStripe\Action\Api\Resource;

use Prometee\PayumStripe\Request\Api\Resource\CreateInterface;
use Prometee\PayumStripe\Request\Api\Resource\CreateSubscription;
use Stripe\Subscription;

class CreateSubscriptionAction extends AbstractCreateAction
{
    /** @var string|Subscription */
    protected $apiResourceClass = Subscription::class;

    /**
     * {@inheritDoc}
     */
    public function supportAlso(CreateInterface $request): bool
    {
        return $request instanceof CreateSubscription;
    }
}
