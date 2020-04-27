<?php

declare(strict_types=1);

namespace Prometee\PayumStripe\Action\Api\Resource;

use Prometee\PayumStripe\Request\Api\Resource\RetrieveSubscription;
use Stripe\Subscription;

final class RetrieveSubscriptionAction extends AbstractRetrieveAction
{
    /** @var string|Subscription */
    protected $apiResourceClass = Subscription::class;

    /**
     * {@inheritDoc}
     */
    public function supportAlso($request): bool
    {
        return $request instanceof RetrieveSubscription;
    }
}
