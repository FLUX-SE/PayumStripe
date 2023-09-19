<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\CreateInterface;
use FluxSE\PayumStripe\Request\Api\Resource\CreateRefund;
use Stripe\Service\AbstractService;
use Stripe\StripeClient;

final class CreateRefundAction extends AbstractCreateAction
{
    public function getStripeService(StripeClient $stripeClient): AbstractService
    {
        return $stripeClient->refunds;
    }

    public function supportAlso(CreateInterface $request): bool
    {
        return $request instanceof CreateRefund;
    }
}
