<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\WebhookEvent;

use Stripe\Event;
use Stripe\PaymentIntent;

final class PaymentIntentAuthorizedSucceedAction extends AbstractPaymentAction
{
    protected function getSupportedEventTypes(): array
    {
        return [
            Event::PAYMENT_INTENT_SUCCEEDED,
        ];
    }

    public function supports($request): bool
    {
        $supports = parent::supports($request);
        if (false === $supports) {
            return false;
        }

        /** @var PaymentIntent $paymentIntent */
        $paymentIntent = $this->retrieveSessionModeObject($request);

        return 'manual' === $paymentIntent->capture_method;
    }
}
