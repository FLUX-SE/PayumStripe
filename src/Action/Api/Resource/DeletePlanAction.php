<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\DeleteInterface;
use FluxSE\PayumStripe\Request\Api\Resource\DeletePlan;
use Stripe\Service\AbstractService;
use Stripe\StripeClient;

final class DeletePlanAction extends AbstractDeleteAction
{
    public function getStripeService(StripeClient $stripeClient): AbstractService
    {
        return $stripeClient->plans;
    }

    public function supportAlso(DeleteInterface $request): bool
    {
        return $request instanceof DeletePlan;
    }
}
