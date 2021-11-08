<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\UpdateInterface;
use FluxSE\PayumStripe\Request\Api\Resource\UpdateCoupon;
use Stripe\Coupon;

final class UpdateCouponAction extends AbstractUpdateAction
{
    protected $apiResourceClass = Coupon::class;

    public function supportAlso(UpdateInterface $request): bool
    {
        return $request instanceof UpdateCoupon;
    }
}
