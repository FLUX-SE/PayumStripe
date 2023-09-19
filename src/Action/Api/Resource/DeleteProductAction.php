<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\DeleteInterface;
use FluxSE\PayumStripe\Request\Api\Resource\DeleteProduct;
use Stripe\Service\AbstractService;
use Stripe\StripeClient;

final class DeleteProductAction extends AbstractDeleteAction
{
    public function getStripeService(StripeClient $stripeClient): AbstractService
    {
        return $stripeClient->products;
    }

    public function supportAlso(DeleteInterface $request): bool
    {
        return $request instanceof DeleteProduct;
    }
}
