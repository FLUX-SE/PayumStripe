<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\AllInterface;
use FluxSE\PayumStripe\Request\Api\Resource\AllPrice;
use Stripe\Service\AbstractService;
use Stripe\StripeClient;

final class AllPriceAction extends AbstractAllAction
{
    public function getStripeService(StripeClient $stripeClient): AbstractService
    {
        return $stripeClient->prices;
    }

    public function supportAlso(AllInterface $request): bool
    {
        return $request instanceof AllPrice;
    }
}
