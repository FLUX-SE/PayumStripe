<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\UpdateInterface;
use FluxSE\PayumStripe\Request\Api\Resource\UpdateSubscription;
use Stripe\Subscription;

final class UpdateSubscriptionAction extends AbstractUpdateAction
{
    protected $apiResourceClass = Subscription::class;

    public function supportAlso(UpdateInterface $request): bool
    {
        return $request instanceof UpdateSubscription;
    }
}
