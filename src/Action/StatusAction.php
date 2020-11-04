<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;
use Stripe\Checkout\Session;
use Stripe\PaymentIntent;
use Stripe\Refund;
use Stripe\SetupIntent;
use Stripe\Subscription;

class StatusAction implements ActionInterface
{
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if ($model['error']) {
            $request->markFailed();

            return;
        }

        // PaymentIntent, Subscription, SetupIntent are the only object name allowed
        // if it's a Session this means the process has been stop somewhere and the
        // payment has to be retried
        if (Session::OBJECT_NAME === $model['object']) {
            $request->markFailed();

            return;
        }

        if (false == $model['status']) {
            $request->markNew();

            return;
        }

        if ($this->isMarkedSessionMode($model, $request)) {
            return;
        }

        if ($this->isMarkedRefund($model, $request)) {
            return;
        }

        $request->markUnknown();
    }

    protected function isMarkedSessionMode(
        ArrayObject $model,
        GetStatusInterface $request
    ): bool {
        if ($this->isMarkedPaymentIntent($model, $request)) {
            return true;
        }

        if ($this->isMarkedSubscription($model, $request)) {
            return true;
        }

        if ($this->isMarkedSetupIntent($model, $request)) {
            return true;
        }

        return false;
    }

    protected function isMarkedPaymentIntent(
        ArrayObject $model,
        GetStatusInterface $request
    ): bool {
        if (PaymentIntent::OBJECT_NAME !== $model['object']) {
            return false;
        }

        $status = (string) $model->offsetGet('status');
        if (PaymentIntent::STATUS_PROCESSING === $status) {
            $request->markPending();

            return true;
        }

        if ($this->isPaymentIntentCanceledStatus($status)) {
            $request->markCanceled();

            return true;
        }

        if (PaymentIntent::STATUS_SUCCEEDED === $status) {
            $request->markCaptured();

            return true;
        }

        if ($this->isPaymentIntentNewStatus($status)) {
            $request->markNew();

            return true;
        }

        return false;
    }

    /**
     * @see https://stripe.com/docs/payments/intents#payment-intent
     */
    protected function isPaymentIntentCanceledStatus(string $status): bool
    {
        return in_array($status, [
            PaymentIntent::STATUS_REQUIRES_PAYMENT_METHOD, // Customer use the "cancel_url"
            PaymentIntent::STATUS_CANCELED,
        ]);
    }

    protected function isPaymentIntentNewStatus(string $status): bool
    {
        return in_array($status, [
            PaymentIntent::STATUS_REQUIRES_CONFIRMATION,
            PaymentIntent::STATUS_REQUIRES_ACTION,
        ]);
    }

    protected function isMarkedSubscription(
        ArrayObject $model,
        GetStatusInterface $request
    ): bool {
        if (Subscription::OBJECT_NAME !== $model['object']) {
            return false;
        }

        $status = (string) $model->offsetGet('status');
        if ($this->isSubscriptionCanceledStatus($status)) {
            $request->markCanceled();

            return true;
        }

        if ($this->isSubscriptionCapturedStatus($status)) {
            $request->markCaptured();

            return true;
        }

        return false;
    }

    protected function isSubscriptionCanceledStatus(string $status): bool
    {
        return in_array($status, [
            Subscription::STATUS_INCOMPLETE, // Customer use the "cancel_url"
            Subscription::STATUS_INCOMPLETE_EXPIRED, // Customer use the "cancel_url" after 23h (weird but possible)
            Subscription::STATUS_CANCELED,
        ]);
    }

    protected function isSubscriptionCapturedStatus(string $status): bool
    {
        return in_array($status, [
            Subscription::STATUS_ACTIVE,
            Subscription::STATUS_TRIALING,
        ]);
    }

    protected function isMarkedSetupIntent(
        ArrayObject $model,
        GetStatusInterface $request
    ): bool {
        if (SetupIntent::OBJECT_NAME !== $model['object']) {
            return false;
        }

        $status = (string) $model->offsetGet('status');
        if (SetupIntent::STATUS_PROCESSING === $status) {
            $request->markPending();

            return true;
        }

        if ($this->isSetupIntentCanceledStatus($status)) {
            $request->markCanceled();

            return true;
        }

        if (SetupIntent::STATUS_SUCCEEDED === $status) {
            $request->markCaptured();

            return true;
        }

        if ($this->isSetupIntentNewStatus($status)) {
            $request->markNew();

            return true;
        }

        return false;
    }

    /**
     * @see https://stripe.com/docs/payments/intents#payment-intent
     */
    protected function isSetupIntentCanceledStatus(string $status): bool
    {
        return in_array($status, [
            SetupIntent::STATUS_REQUIRES_PAYMENT_METHOD, // Customer use the "cancel_url"
            SetupIntent::STATUS_CANCELED,
        ]);
    }

    protected function isSetupIntentNewStatus(string $status): bool
    {
        return in_array($status, [
            SetupIntent::STATUS_REQUIRES_CONFIRMATION,
            SetupIntent::STATUS_REQUIRES_ACTION,
        ]);
    }

    protected function isMarkedRefund(
        ArrayObject $model,
        GetStatusInterface $request
    ): bool {
        if (Refund::OBJECT_NAME !== $model['object']) {
            return false;
        }

        $status = (string) $model->offsetGet('status');
        if (Refund::STATUS_SUCCEEDED === $status) {
            $request->markRefunded();

            return true;
        }

        return false;
    }

    public function supports($request): bool
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getModel() instanceof ArrayAccess
            ;
    }
}
