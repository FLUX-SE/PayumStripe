<?php

declare(strict_types=1);

namespace Prometee\PayumStripeCheckoutSession\Action;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;
use Stripe\PaymentIntent;
use Stripe\Refund;

class StatusAction implements ActionInterface
{
    /**
     * {@inheritDoc}
     *
     * @param GetStatusInterface $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if ($model['error']) {
            $request->markFailed();
            return;
        }

        if (false == $model['status']) {
            $request->markNew();
            return;
        }

        if ($this->isAPaymentIntentAndHasBeenMarked($model, $request)) {
            return;
        }

        if ($this->isARefundAndHasBeenMarked($model, $request)) {
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
    protected function isAPaymentIntentAndHasBeenMarked(
        ArrayObject $model,
        GetStatusInterface $request
    ): bool {
        if ($model['object'] !== PaymentIntent::OBJECT_NAME) {
            return false;
        }

        if (PaymentIntent::STATUS_PROCESSING === $model['status']) {
            $request->markPending();
            return true;
        }

        if ($this->isACanceledStatus($model['status'])) {
            $request->markCanceled();
            return true;
        }

        if (PaymentIntent::STATUS_SUCCEEDED === $model['status']) {
            $request->markCaptured();
            return true;
        }

        if ($this->isANewStatus($model['status'])) {
            $request->markNew();
            return true;
        }

        return false;
    }

    /**
     * @param ArrayObject $model
     * @param GetStatusInterface $request
     *
     * @return bool
     */
    protected function isARefundAndHasBeenMarked(
        ArrayObject $model,
        GetStatusInterface $request
    ): bool {
        if ($model['object'] !== Refund::OBJECT_NAME) {
            return false;
        }

        if (Refund::STATUS_SUCCEEDED === $model['status']) {
            $request->markRefunded();
            return true;
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getModel() instanceof ArrayAccess
            ;
    }

    /**
     * @param string $status
     *
     * @return bool
     *
     * @see https://stripe.com/docs/payments/intents#payment-intent
     */
    protected function isACanceledStatus(string $status): bool
    {
        return in_array($status, [
            PaymentIntent::STATUS_CANCELED,
        ]);
    }

    /**
     * @param string $status
     *
     * @return bool
     */
    protected function isANewStatus(string $status): bool
    {
        return in_array($status, [
            PaymentIntent::STATUS_REQUIRES_PAYMENT_METHOD,
            PaymentIntent::STATUS_REQUIRES_CONFIRMATION,
            PaymentIntent::STATUS_REQUIRES_ACTION,
        ]);
    }
}
