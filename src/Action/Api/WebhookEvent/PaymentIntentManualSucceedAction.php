<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\WebhookEvent;

use Payum\Core\Exception\RequestNotSupportedException;
use Stripe\Event;
use Stripe\PaymentIntent;
use Stripe\StripeObject;

final class PaymentIntentManualSucceedAction extends AbstractPaymentAction
{
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var StripeObject&PaymentIntent $paymentIntent */
        $paymentIntent = $this->retrieveSessionModeObject($request);

        // This WebhookEvent is dedicated to the Authorize flow
        if ('manual' !== $paymentIntent->capture_method) {
            return;
        }

        parent::execute($request);
    }

    protected function getSupportedEventTypes(): array
    {
        return [
            Event::PAYMENT_INTENT_SUCCEEDED,
        ];
    }
}
