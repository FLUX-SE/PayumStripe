<?php

declare(strict_types=1);

namespace Prometee\PayumStripe\Action\Api\Resource;

use Prometee\PayumStripe\Request\Api\Resource\CreateInterface;
use Prometee\PayumStripe\Request\Api\Resource\CreatePlan;
use Stripe\Plan;

class CreatePlanAction extends AbstractCreateAction
{
    /** @var string|Plan */
    protected $apiResourceClass = Plan::class;

    /**
     * {@inheritDoc}
     */
    public function supportAlso(CreateInterface $request): bool
    {
        return $request instanceof CreatePlan;
    }
}
