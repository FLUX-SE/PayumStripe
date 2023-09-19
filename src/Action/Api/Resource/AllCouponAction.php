<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\AllCoupon;
use FluxSE\PayumStripe\Request\Api\Resource\AllInterface;
use Stripe\Service\AbstractService;
use Stripe\StripeClient;

final class AllCouponAction extends AbstractAllAction
{
    public function getStripeService(StripeClient $stripeClient): AbstractService
    {
        return $stripeClient->coupons;
    }

    public function supportAlso(AllInterface $request): bool
    {
        return $request instanceof AllCoupon;
    }
}
