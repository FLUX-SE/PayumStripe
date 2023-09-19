<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\DeleteCoupon;
use FluxSE\PayumStripe\Request\Api\Resource\DeleteInterface;
use Stripe\Service\AbstractService;
use Stripe\StripeClient;

final class DeleteCouponAction extends AbstractDeleteAction
{
    public function getStripeService(StripeClient $stripeClient): AbstractService
    {
        return $stripeClient->coupons;
    }

    public function supportAlso(DeleteInterface $request): bool
    {
        return $request instanceof DeleteCoupon;
    }
}
