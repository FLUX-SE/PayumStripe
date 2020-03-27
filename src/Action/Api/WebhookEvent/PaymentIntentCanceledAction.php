<?php

declare(strict_types=1);

namespace Prometee\PayumStripeCheckoutSession\Action\Api\WebhookEvent;

use Stripe\Event;

final class PaymentIntentCanceledAction extends AbstractPaymentAction
{
    /**
     * {@inheritDoc}
     */
    protected function getSupportedEventTypes(): array
    {
        return [
            Event::PAYMENT_INTENT_CANCELED
        ];
    }
}
