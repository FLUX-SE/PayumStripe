<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\WebhookEvent;

use Stripe\Event;

final class SetupIntentCanceledAction extends AbstractPaymentAction
{
    /**
     * {@inheritdoc}
     */
    protected function getSupportedEventTypes(): array
    {
        return [
            Event::SETUP_INTENT_CANCELED,
        ];
    }
}
