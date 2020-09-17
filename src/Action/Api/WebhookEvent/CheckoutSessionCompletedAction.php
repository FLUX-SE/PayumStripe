<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\WebhookEvent;

use Stripe\Event;

final class CheckoutSessionCompletedAction extends AbstractPaymentAction
{
    /**
     * {@inheritdoc}
     */
    protected function getSupportedEventTypes(): array
    {
        return [
            Event::CHECKOUT_SESSION_COMPLETED,
        ];
    }
}
