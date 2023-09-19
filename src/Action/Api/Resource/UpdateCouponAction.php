<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\UpdateCoupon;
use FluxSE\PayumStripe\Request\Api\Resource\UpdateInterface;
use Stripe\Service\AbstractService;
use Stripe\StripeClient;

final class UpdateCouponAction extends AbstractUpdateAction
{
    public function getStripeService(StripeClient $stripeClient): AbstractService
    {
        return $stripeClient->coupons;
    }

    public function supportAlso(UpdateInterface $request): bool
    {
        return $request instanceof UpdateCoupon;
    }
}
