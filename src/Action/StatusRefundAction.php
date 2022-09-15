<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\GetStatusInterface;
use Stripe\Refund;

class StatusRefundAction extends AbstractStatusAction
{
    public function isMarkedStatus(GetStatusInterface $request, ArrayObject $model): bool
    {
        /** @var string|null $status */
        $status = $model->offsetGet('status');
        if (null === $status) {
            return false;
        }

        if (Refund::STATUS_CANCELED === $status) {
            $request->markCanceled();

            return true;
        }
        if (Refund::STATUS_PENDING === $status) {
            $request->markPending();

            return true;
        }
        if (Refund::STATUS_FAILED === $status) {
            $request->markFailed();

            return true;
        }
        if (Refund::STATUS_SUCCEEDED === $status) {
            $request->markRefunded();

            return true;
        }

        return false;
    }

    public function getSupportedObjectName(): string
    {
        return Refund::OBJECT_NAME;
    }
}
