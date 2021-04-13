<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\CreateInterface;
use FluxSE\PayumStripe\Request\Api\Resource\CreatePaymentIntent;
use Stripe\PaymentIntent;

final class CreatePaymentIntentAction extends AbstractCreateAction
{
    protected $apiResourceClass = PaymentIntent::class;

    public function supportAlso(CreateInterface $request): bool
    {
        return $request instanceof CreatePaymentIntent;
    }
}
