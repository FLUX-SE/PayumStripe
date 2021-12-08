<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Extension\StripeCheckoutSession;

use FluxSE\PayumStripe\Request\Api\Resource\AbstractCustomCall;
use FluxSE\PayumStripe\Request\Api\Resource\CancelPaymentIntent;
use Stripe\PaymentIntent;

/**
 * Detect if we are into the `done` phase and the payment status is still new.
 * It means the customer click on the cancel URL button, and the PaymentIntent
 * must be canceled to avoid the Checkout Session to be paid in any way.
 *
 * @see https://github.com/FLUX-SE/SyliusPayumStripePlugin/issues/32
 */
final class CancelUrlCancelPaymentIntentExtension extends AbstractCancelUrlExtension
{
    public function getSupportedObjectName(): string
    {
        return PaymentIntent::OBJECT_NAME;
    }

    public function createNewRequest(string $id): AbstractCustomCall
    {
        return new CancelPaymentIntent($id);
    }
}
