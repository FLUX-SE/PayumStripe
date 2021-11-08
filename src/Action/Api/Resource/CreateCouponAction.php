<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\CreateCoupon;
use FluxSE\PayumStripe\Request\Api\Resource\CreateInterface;
use Stripe\Coupon;

final class CreateCouponAction extends AbstractCreateAction
{
    protected $apiResourceClass = Coupon::class;

    public function supportAlso(CreateInterface $request): bool
    {
        return $request instanceof CreateCoupon;
    }
}
