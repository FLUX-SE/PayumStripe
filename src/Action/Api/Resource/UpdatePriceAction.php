<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\UpdateInterface;
use FluxSE\PayumStripe\Request\Api\Resource\UpdatePrice;
use Stripe\Service\AbstractService;
use Stripe\StripeClient;

final class UpdatePriceAction extends AbstractUpdateAction
{
    public function getStripeService(StripeClient $stripeClient): AbstractService
    {
        return $stripeClient->prices;
    }

    public function supportAlso(UpdateInterface $request): bool
    {
        return $request instanceof UpdatePrice;
    }
}
