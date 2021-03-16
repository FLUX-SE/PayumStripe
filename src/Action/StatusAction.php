<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;
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

        // This should be possible only when we have a previous payment
        // with a different gateway
        if (false === $model->offsetExists('object')) {
            $request->markNew();

            return;
        }

        if (null !== $model->offsetGet('error')) {
            $request->markFailed();

            return;
        }

        // PaymentIntent, Subscription, SetupIntent and Refund are the only object allowed here
        // if it's a Session this means the process has been stop somewhere and the
        // payment has to be retried so mark it as failed to allow retrying it
        $allowedObjectName = [
            PaymentIntent::OBJECT_NAME,
            SetupIntent::OBJECT_NAME,
            Subscription::OBJECT_NAME,
            Refund::OBJECT_NAME,
        ];
        if (false === in_array($model->offsetGet('object'), $allowedObjectName)) {
            $request->markFailed();

            return;
        }

        /*
         * @see StatusPaymentIntentAction for PaymentIntent status changes
         * @see StatusSetupIntentAction for SetupIntent status changes
         * @see StatusSubscriptionAction for SubscriptionIntent status changes
         * @see StatusRefundAction for RefundIntent status changes
         */

        $request->markUnknown();
    }

    public function supports($request): bool
    {
        if (false === $request instanceof GetStatusInterface) {
            return false;
        }

        return $request->getModel() instanceof ArrayAccess;
    }
}
