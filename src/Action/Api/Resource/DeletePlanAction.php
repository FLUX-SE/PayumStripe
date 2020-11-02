<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\DeleteInterface;
use FluxSE\PayumStripe\Request\Api\Resource\DeletePlan;
use Stripe\Plan;

final class DeletePlanAction extends AbstractDeleteAction
{
    protected $apiResourceClass = Plan::class;

    public function supportAlso(DeleteInterface $request): bool
    {
        return $request instanceof DeletePlan;
    }
}
