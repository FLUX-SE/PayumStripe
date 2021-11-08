<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\AllCoupon;
use FluxSE\PayumStripe\Request\Api\Resource\AllInterface;
use Stripe\Coupon;

final class AllCouponAction extends AbstractAllAction
{
    protected $apiResourceClass = Coupon::class;

    public function supportAlso(AllInterface $request): bool
    {
        return $request instanceof AllCoupon;
    }
}
