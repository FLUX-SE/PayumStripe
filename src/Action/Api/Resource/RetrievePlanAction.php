<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\RetrieveInterface;
use FluxSE\PayumStripe\Request\Api\Resource\RetrievePlan;
use Stripe\Plan;

final class RetrievePlanAction extends AbstractRetrieveAction
{
    protected $apiResourceClass = Plan::class;

    public function supportAlso(RetrieveInterface $request): bool
    {
        return $request instanceof RetrievePlan;
    }
}
