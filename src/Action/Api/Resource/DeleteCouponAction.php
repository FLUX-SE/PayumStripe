<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\DeleteCoupon;
use FluxSE\PayumStripe\Request\Api\Resource\DeleteInterface;
use Stripe\Coupon;

final class DeleteCouponAction extends AbstractDeleteAction
{
    protected $apiResourceClass = Coupon::class;

    public function supportAlso(DeleteInterface $request): bool
    {
        return $request instanceof DeleteCoupon;
    }
}
