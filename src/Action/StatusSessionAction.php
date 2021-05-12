<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\GetStatusInterface;
use Stripe\Checkout\Session;

class StatusSessionAction extends AbstractStatusAction
{
    public function isMarkedStatus(GetStatusInterface $request, ArrayObject $model): bool
    {
        if (Session::OBJECT_NAME !== $model->offsetGet('object')) {
            $this->gateway->execute($request);

            return true;
        }

        $status = (string) $model->offsetGet('payment_status');
        if ($this->isNewStatus($status)) {
            $request->markNew();

            return true;
        }

        return false;
    }

    protected function isNewStatus(string $status): bool
    {
        return in_array($status, [
            Session::PAYMENT_STATUS_NO_PAYMENT_REQUIRED,
            Session::PAYMENT_STATUS_UNPAID,
        ]);
    }

    public function getSupportedObjectName(): string
    {
        return Session::OBJECT_NAME;
    }
}
