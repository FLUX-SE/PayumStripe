<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\GetStatusInterface;
use Stripe\PaymentIntent;

class StatusPaymentIntentAction implements ActionInterface
{
    use StatusActionTrait;

    public function isMarkedStatus(GetStatusInterface $request, ArrayObject $model): bool
    {
        $status = (string) $model->offsetGet('status');
        if (PaymentIntent::STATUS_SUCCEEDED === $status) {
            $request->markCaptured();

            return true;
        }

        if (PaymentIntent::STATUS_PROCESSING === $status) {
            $request->markPending();

            return true;
        }

        if ($this->isCanceledStatus($status)) {
            $request->markCanceled();

            return true;
        }

        if ($this->isNewStatus($status)) {
            $request->markNew();

            return true;
        }

        return false;
    }

    /**
     * @see https://stripe.com/docs/payments/intents#payment-intent
     */
    protected function isCanceledStatus(string $status): bool
    {
        return in_array($status, [
            PaymentIntent::STATUS_REQUIRES_PAYMENT_METHOD, // Customer use the "cancel_url"
            PaymentIntent::STATUS_CANCELED,
        ]);
    }

    protected function isNewStatus(string $status): bool
    {
        return in_array($status, [
            PaymentIntent::STATUS_REQUIRES_CONFIRMATION,
            PaymentIntent::STATUS_REQUIRES_ACTION,
        ]);
    }

    public function getSupportedObjectName(): string
    {
        return PaymentIntent::OBJECT_NAME;
    }
}
