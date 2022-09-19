<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Extension\StripeCheckoutSession;

use FluxSE\PayumStripe\Request\Api\Resource\AbstractCustomCall;
use FluxSE\PayumStripe\Request\Api\Resource\AllSession;
use FluxSE\PayumStripe\Request\Api\Resource\ExpireSession;
use Payum\Core\Extension\Context;
use Stripe\Checkout\Session;
use Stripe\PaymentIntent;

/**
 * Detect if we are into the `done` phase and the payment status is still new.
 * It means the customer click on the cancel URL button, and the PaymentIntent
 * must be canceled to avoid the Checkout Session to be paid in any way.
 *
 * UPDATE [09/2022] : Instead of canceling the PaymentIntent now it will Expire the related session
 *
 * @see https://stripe.com/docs/api/payment_intents/cancel
 * You cannot cancel the PaymentIntent for a Checkout Session. Expire the Checkout Session instead
 * @see https://github.com/FLUX-SE/SyliusPayumStripePlugin/issues/32
 */
final class CancelUrlCancelPaymentIntentExtension extends AbstractCancelUrlExtension
{
    public function getSupportedObjectName(): string
    {
        return PaymentIntent::OBJECT_NAME;
    }

    public function createNewRequest(string $id, Context $context): ?AbstractCustomCall
    {
        // @link https://stripe.com/docs/api/payment_intents/cancel
        // > You cannot cancel the PaymentIntent for a Checkout Session.
        // > Expire the Checkout Session instead.
        $gateway = $context->getGateway();
        $request = new AllSession([
            'payment_intent' => $id,
        ]);
        $gateway->execute($request);

        $sessions = $request->getApiResources();
        /** @var Session|null $session */
        $session = $sessions->first();
        if (null === $session) {
            return null;
        }

        return new ExpireSession($session->id);
    }
}
