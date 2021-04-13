<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\CreateInterface;
use FluxSE\PayumStripe\Request\Api\Resource\CreatePlan;
use Stripe\Plan;

final class CreatePlanAction extends AbstractCreateAction
{
    protected $apiResourceClass = Plan::class;

    public function supportAlso(CreateInterface $request): bool
    {
        return $request instanceof CreatePlan;
    }
}
