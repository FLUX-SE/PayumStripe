<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\StripeCheckoutSession\Api\WebhookEvent;

use FluxSE\PayumStripe\Action\Api\WebhookEvent\AbstractPaymentAction;
use Stripe\Event;

final class CheckoutSessionAsyncPaymentSucceededAction extends AbstractPaymentAction
{
    protected function getSupportedEventTypes(): array
    {
        return [
            Event::CHECKOUT_SESSION_ASYNC_PAYMENT_SUCCEEDED,
        ];
    }
}
