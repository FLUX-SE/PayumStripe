<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\RetrieveInterface;
use FluxSE\PayumStripe\Request\Api\Resource\RetrieveProduct;
use Stripe\Product;

final class RetrieveProductAction extends AbstractRetrieveAction
{
    protected $apiResourceClass = Product::class;

    public function supportAlso(RetrieveInterface $request): bool
    {
        return $request instanceof RetrieveProduct;
    }
}
