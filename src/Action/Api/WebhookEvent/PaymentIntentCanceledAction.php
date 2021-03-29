<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\WebhookEvent;

use FluxSE\PayumStripe\Action\TokenHashKeysInterface;
use Stripe\Event;

final class PaymentIntentCanceledAction extends AbstractPaymentAction
{
    protected function getSupportedEventTypes(): array
    {
        return [
            Event::PAYMENT_INTENT_CANCELED,
        ];
    }

    public function getTokenHashMetadataKeyName(): string
    {
        return TokenHashKeysInterface::CANCEL_TOKEN_HASH_KEY_NAME;
    }
}
