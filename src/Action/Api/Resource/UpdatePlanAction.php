<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\UpdateInterface;
use FluxSE\PayumStripe\Request\Api\Resource\UpdatePlan;
use Stripe\Service\AbstractService;
use Stripe\StripeClient;

final class UpdatePlanAction extends AbstractUpdateAction
{
    public function getStripeService(StripeClient $stripeClient): AbstractService
    {
        return $stripeClient->plans;
    }

    public function supportAlso(UpdateInterface $request): bool
    {
        return $request instanceof UpdatePlan;
    }
}
