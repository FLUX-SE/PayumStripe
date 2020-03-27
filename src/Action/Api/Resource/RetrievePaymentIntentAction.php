<?php

declare(strict_types=1);

namespace Prometee\PayumStripeCheckoutSession\Action\Api\Resource;

use Prometee\PayumStripeCheckoutSession\Request\Api\Resource\RetrievePaymentIntent;
use Stripe\PaymentIntent;

final class RetrievePaymentIntentAction extends AbstractRetrieveAction
{
    /**
     * {@inheritDoc}
     */
    public function getApiResourceClass(): string
    {
        return PaymentIntent::class;
    }

    /**
     * {@inheritDoc}
     */
    public function supportAlso($request): bool
    {
        return $request instanceof RetrievePaymentIntent;
    }
}
