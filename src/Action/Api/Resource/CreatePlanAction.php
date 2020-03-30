<?php

declare(strict_types=1);

namespace Prometee\PayumStripeCheckoutSession\Action\Api\Resource;

use Prometee\PayumStripeCheckoutSession\Request\Api\Resource\CreatePlan;
use Stripe\Plan;

class CreatePlanAction extends AbstractCreateAction
{
    /** @var string|Plan */
    protected $apiResourceClass = Plan::class;

    /**
     * {@inheritDoc}
     */
    public function supportAlso($request): bool
    {
        return $request instanceof CreatePlan;
    }
}
