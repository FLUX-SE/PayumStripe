<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\UpdateInterface;
use FluxSE\PayumStripe\Request\Api\Resource\UpdatePaymentIntent;
use Stripe\Service\AbstractService;
use Stripe\StripeClient;

final class UpdatePaymentIntentAction extends AbstractUpdateAction
{
    public function getStripeService(StripeClient $stripeClient): AbstractService
    {
        return $stripeClient->paymentIntents;
    }

    public function supportAlso(UpdateInterface $request): bool
    {
        return $request instanceof UpdatePaymentIntent;
    }
}
