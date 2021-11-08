<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\RetrieveCoupon;
use FluxSE\PayumStripe\Request\Api\Resource\RetrieveInterface;
use Stripe\Coupon;

final class RetrieveCouponAction extends AbstractRetrieveAction
{
    protected $apiResourceClass = Coupon::class;

    public function supportAlso(RetrieveInterface $request): bool
    {
        return $request instanceof RetrieveCoupon;
    }
}
