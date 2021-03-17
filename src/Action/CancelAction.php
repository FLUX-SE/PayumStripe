<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action;

use ArrayAccess;
use FluxSE\PayumStripe\Request\Api\Resource\CancelPaymentIntent;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Cancel;
use Stripe\PaymentIntent;

final class CancelAction extends AbstractPaymentIntentAwareAction
{
    /**
     * @param Cancel $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $paymentIntent = $this->preparePaymentIntent($request);
        if (null === $paymentIntent) {
            return;
        }

        $cancelRequest = new CancelPaymentIntent($paymentIntent->id);
        $this->gateway->execute($cancelRequest);

        /** @var PaymentIntent $paymentIntent */
        $paymentIntent = $cancelRequest->getApiResource();
        $request->setModel($paymentIntent->toArray());
    }

    public function supports($request): bool
    {
        if (false === $request instanceof Cancel) {
            return false;
        }

        return $request->getModel() instanceof ArrayAccess;
    }
}
