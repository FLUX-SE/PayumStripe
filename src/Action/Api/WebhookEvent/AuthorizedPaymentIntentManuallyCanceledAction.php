<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\WebhookEvent;

use FluxSE\PayumStripe\Token\TokenHashKeysInterface;
use Stripe\Event;
use Stripe\PaymentIntent;

final class AuthorizedPaymentIntentManuallyCanceledAction extends AbstractPaymentIntentAction
{
    protected function getSupportedEventTypes(): array
    {
        return [
            Event::PAYMENT_INTENT_CANCELED,
        ];
    }

    protected function getSupportedCaptureMethod(): string
    {
        return PaymentIntent::CAPTURE_METHOD_MANUAL;
    }

    public function getTokenHashMetadataKeyName(): string
    {
        return TokenHashKeysInterface::CANCEL_TOKEN_HASH_KEY_NAME;
    }
}
