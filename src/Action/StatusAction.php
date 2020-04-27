<?php

declare(strict_types=1);

namespace Prometee\PayumStripe\Action;

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
    /**
     * {@inheritDoc}
     *
     * @param GetStatusInterface $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if ($model['error']) {
            $request->markFailed();
            return;
        }

        if ($model['object'] === Session::OBJECT_NAME) {
            $request->markFailed();
            return;
        }

        if (false == $model['status']) {
            $request->markNew();
            return;
        }

        if ($this->isMarkedPaymentIntent($model, $request)) {
            return;
        }

        if ($this->isMarkedSubscription($model, $request)) {
            return;
        }

        if ($this->isMarkedSetupIntent($model, $request)) {
            return;
        }

        if ($this->isMarkedRefund($model, $request)) {
            return;
        }

        $request->markUnknown();
    }

    /**
     * @param ArrayObject $model
     * @param GetStatusInterface $request
     *
     * @return bool
     */
    protected function isMarkedPaymentIntent(
        ArrayObject $model,
        GetStatusInterface $request
    ): bool {
        if ($model['object'] !== PaymentIntent::OBJECT_NAME) {
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
     * @param string $status
     *
     * @return bool
     *
     * @see https://stripe.com/docs/payments/intents#payment-intent
     */
    protected function isPaymentIntentCanceledStatus(string $status): bool
    {
        return in_array($status, [
            PaymentIntent::STATUS_REQUIRES_PAYMENT_METHOD, // Customer use the "cancel_url"
            PaymentIntent::STATUS_CANCELED,
        ]);
    }

    /**
     * @param string $status
     *
     * @return bool
     */
    protected function isPaymentIntentNewStatus(string $status): bool
    {
        return in_array($status, [
            PaymentIntent::STATUS_REQUIRES_CONFIRMATION,
            PaymentIntent::STATUS_REQUIRES_ACTION,
        ]);
    }

    /**
     * @param ArrayObject $model
     * @param GetStatusInterface $request
     *
     * @return bool
     */
    protected function isMarkedSubscription(
        ArrayObject $model,
        GetStatusInterface $request
    ): bool {
        if ($model['object'] !== Subscription::OBJECT_NAME) {
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

    /**
     * @param string $status
     *
     * @return bool
     */
    protected function isSubscriptionCanceledStatus(string $status): bool
    {
        return in_array($status, [
            Subscription::STATUS_INCOMPLETE, // Customer use the "cancel_url"
            Subscription::STATUS_INCOMPLETE_EXPIRED, // Customer use the "cancel_url" after 23h (weird but possible)
            Subscription::STATUS_CANCELED,
        ]);
    }

    /**
     * @param string $status
     *
     * @return bool
     */
    protected function isSubscriptionCapturedStatus(string $status): bool
    {
        return in_array($status, [
            Subscription::STATUS_ACTIVE,
            Subscription::STATUS_TRIALING,
        ]);
    }

    /**
     * @param ArrayObject $model
     * @param GetStatusInterface $request
     *
     * @return bool
     */
    protected function isMarkedSetupIntent(
        ArrayObject $model,
        GetStatusInterface $request
    ): bool {
        if ($model['object'] !== SetupIntent::OBJECT_NAME) {
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
     * @param string $status
     *
     * @return bool
     *
     * @see https://stripe.com/docs/payments/intents#payment-intent
     */
    protected function isSetupIntentCanceledStatus(string $status): bool
    {
        return in_array($status, [
            SetupIntent::STATUS_REQUIRES_PAYMENT_METHOD, // Customer use the "cancel_url"
            SetupIntent::STATUS_CANCELED,
        ]);
    }

    /**
     * @param string $status
     *
     * @return bool
     */
    protected function isSetupIntentNewStatus(string $status): bool
    {
        return in_array($status, [
            SetupIntent::STATUS_REQUIRES_CONFIRMATION,
            SetupIntent::STATUS_REQUIRES_ACTION,
        ]);
    }
    /**
     * @param ArrayObject $model
     * @param GetStatusInterface $request
     *
     * @return bool
     */
    protected function isMarkedRefund(
        ArrayObject $model,
        GetStatusInterface $request
    ): bool {
        if ($model['object'] !== Refund::OBJECT_NAME) {
            return false;
        }

        $status = (string) $model->offsetGet('status');
        if (Refund::STATUS_SUCCEEDED === $status) {
            $request->markRefunded();
            return true;
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request): bool
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getModel() instanceof ArrayAccess
            ;
    }
}
