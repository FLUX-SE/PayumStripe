<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\CreateInterface;
use FluxSE\PayumStripe\Request\Api\Resource\CreateSubscription;
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
