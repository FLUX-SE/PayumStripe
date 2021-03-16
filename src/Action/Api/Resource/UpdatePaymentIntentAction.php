<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\UpdateInterface;
use FluxSE\PayumStripe\Request\Api\Resource\UpdatePaymentIntent;
use Stripe\PaymentIntent;

final class UpdatePaymentIntentAction extends AbstractUpdateAction
{
    protected $apiResourceClass = PaymentIntent::class;

    public function supportAlso(UpdateInterface $request): bool
    {
        return $request instanceof UpdatePaymentIntent;
    }
}
