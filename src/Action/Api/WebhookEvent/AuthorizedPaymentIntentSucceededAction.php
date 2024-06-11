<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\WebhookEvent;

use FluxSE\PayumStripe\Token\TokenHashKeysInterface;
use Stripe\Event;
use Stripe\PaymentIntent;

final class AuthorizedPaymentIntentSucceededAction extends AbstractPaymentIntentAction
{
    protected function getSupportedEventTypes(): array
    {
        return [
            Event::PAYMENT_INTENT_SUCCEEDED,
        ];
    }

    protected function getSupportedCaptureMethod(): string
    {
        return PaymentIntent::CAPTURE_METHOD_MANUAL;
    }

    public function getTokenHashMetadataKeyName(): string
    {
        return TokenHashKeysInterface::CAPTURE_AUTHORIZE_TOKEN_HASH_KEY_NAME;
    }
}
