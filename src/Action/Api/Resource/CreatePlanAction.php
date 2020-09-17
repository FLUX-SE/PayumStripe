<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\CreateInterface;
use FluxSE\PayumStripe\Request\Api\Resource\CreatePlan;
use Stripe\Plan;

class CreatePlanAction extends AbstractCreateAction
{
    /** @var string|Plan */
    protected $apiResourceClass = Plan::class;

    /**
     * {@inheritdoc}
     */
    public function supportAlso(CreateInterface $request): bool
    {
        return $request instanceof CreatePlan;
    }
}
