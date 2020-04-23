<?php

declare(strict_types=1);

namespace Prometee\PayumStripe\Action\Api\WebhookEvent;

use Stripe\Event;

final class CheckoutSessionCompletedAction extends AbstractPaymentAction
{
    /**
     * {@inheritDoc}
     */
    protected function getSupportedEventTypes(): array
    {
        return [
            Event::CHECKOUT_SESSION_COMPLETED
        ];
    }
}
