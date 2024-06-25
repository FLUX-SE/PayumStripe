<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\GetStatusInterface;
use Stripe\SetupIntent;
use Stripe\StripeObject;

class StatusSetupIntentAction extends AbstractStatusAction
{
    public function isMarkedStatus(GetStatusInterface $request, ArrayObject $model): bool
    {
        /** @var string|null $status */
        $status = $model->offsetGet('status');
        if (null === $status) {
            return false;
        }

        if (SetupIntent::STATUS_SUCCEEDED === $status) {
            $request->markCaptured();

            return true;
        }

        if (SetupIntent::STATUS_PROCESSING === $status) {
            $request->markPending();

            return true;
        }

        if ($this->isCanceledStatus($status) || $this->isSpecialCanceledStatus($model)) {
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
     * @see https://stripe.com/docs/payments/setupintents/lifecycle
     */
    protected function isCanceledStatus(string $status): bool
    {
        return SetupIntent::STATUS_CANCELED === $status;
    }

    protected function isNewStatus(string $status): bool
    {
        return in_array($status, [
            SetupIntent::STATUS_REQUIRES_PAYMENT_METHOD, // Customer use the "cancel_url"
            SetupIntent::STATUS_REQUIRES_CONFIRMATION,
            SetupIntent::STATUS_REQUIRES_ACTION,
        ], true);
    }



    /**
     * @see https://stripe.com/docs/payments/setupintents/lifecycle
     */
    protected function isSpecialCanceledStatus(ArrayObject $model): bool
    {
        /** @var string|null $status */
        $status = $model->offsetGet('status');
        /** @var null|StripeObject $lastPaymentError */
        $lastPaymentError = $model->offsetGet('last_setup_error');

        if (SetupIntent::STATUS_REQUIRES_PAYMENT_METHOD === $status) {
            if (null !== $lastPaymentError) {
                return true;
            }
        }

        return false;
    }

    public function getSupportedObjectName(): string
    {
        return SetupIntent::OBJECT_NAME;
    }
}
