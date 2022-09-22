<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\WebhookEvent;

use Stripe\Event;

final class PaymentIntentSucceededFromAuthorizeAction extends AbstractPaymentIntentAction
{
    protected function getSupportedEventTypes(): array
    {
        return [
            Event::PAYMENT_INTENT_SUCCEEDED,
        ];
    }

    protected function getSupportedCaptureMethod(): string
    {
        return 'manual';
    }
}
