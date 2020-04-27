<?php

declare(strict_types=1);

namespace Prometee\PayumStripe\Action\Api\WebhookEvent;

use Stripe\Event;

final class SetupIntentCanceledAction extends AbstractPaymentAction
{
    /**
     * {@inheritDoc}
     */
    protected function getSupportedEventTypes(): array
    {
        return [
            Event::SETUP_INTENT_CANCELED
        ];
    }
}
