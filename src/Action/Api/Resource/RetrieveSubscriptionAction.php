<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\RetrieveSubscription;
use Stripe\Subscription;

final class RetrieveSubscriptionAction extends AbstractRetrieveAction
{
    /** @var string|Subscription */
    protected $apiResourceClass = Subscription::class;

    /**
     * {@inheritdoc}
     */
    public function supportAlso($request): bool
    {
        return $request instanceof RetrieveSubscription;
    }
}
