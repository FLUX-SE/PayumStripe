<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\UpdateInterface;
use FluxSE\PayumStripe\Request\Api\Resource\UpdateProduct;
use Stripe\Service\AbstractService;
use Stripe\StripeClient;

final class UpdateProductAction extends AbstractUpdateAction
{
    public function getStripeService(StripeClient $stripeClient): AbstractService
    {
        return $stripeClient->products;
    }

    public function supportAlso(UpdateInterface $request): bool
    {
        return $request instanceof UpdateProduct;
    }
}
