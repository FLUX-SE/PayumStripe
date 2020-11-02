<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\RetrieveInterface;
use FluxSE\PayumStripe\Request\Api\Resource\RetrievePaymentIntent;
use Stripe\PaymentIntent;

final class RetrievePaymentIntentAction extends AbstractRetrieveAction
{
    protected $apiResourceClass = PaymentIntent::class;

    public function supportAlso(RetrieveInterface $request): bool
    {
        return $request instanceof RetrievePaymentIntent;
    }
}
