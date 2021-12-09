<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\GetStatusInterface;
use Stripe\Subscription;

class StatusSubscriptionAction extends AbstractStatusAction
{
    public function isMarkedStatus(GetStatusInterface $request, ArrayObject $model): bool
    {
        $status = (string) $model->offsetGet('status');
        if ($this->isCapturedStatus($status)) {
            $request->markCaptured();

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

    protected function isCapturedStatus(string $status): bool
    {
        return in_array($status, [
            Subscription::STATUS_ACTIVE,
            Subscription::STATUS_TRIALING,
        ], true);
    }

    protected function isCanceledStatus(string $status): bool
    {
        return in_array($status, [
            Subscription::STATUS_INCOMPLETE_EXPIRED, // Customer use the "cancel_url" after 23h timeout (weird but possible)
            Subscription::STATUS_CANCELED,
        ], true);
    }

    protected function isNewStatus(string $status): bool
    {
        return Subscription::STATUS_INCOMPLETE === $status;
    }

    public function getSupportedObjectName(): string
    {
        return Subscription::OBJECT_NAME;
    }
}
