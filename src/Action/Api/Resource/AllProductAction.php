<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\AllInterface;
use FluxSE\PayumStripe\Request\Api\Resource\AllProduct;
use Stripe\Service\AbstractService;
use Stripe\StripeClient;

final class AllProductAction extends AbstractAllAction
{
    public function getStripeService(StripeClient $stripeClient): AbstractService
    {
        return $stripeClient->products;
    }

    public function supportAlso(AllInterface $request): bool
    {
        return $request instanceof AllProduct;
    }
}
