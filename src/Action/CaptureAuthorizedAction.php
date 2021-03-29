<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action;

use ArrayObject;
use FluxSE\PayumStripe\Request\Api\Resource\CapturePaymentIntent;
use FluxSE\PayumStripe\Request\CaptureAuthorized;
use Payum\Core\Exception\RequestNotSupportedException;
use Stripe\PaymentIntent;

final class CaptureAuthorizedAction extends AbstractPaymentIntentAwareAction
{
    /**
     * @param CaptureAuthorized $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $paymentIntent = $this->preparePaymentIntent($request);
        if (null === $paymentIntent) {
            return;
        }

        if (PaymentIntent::STATUS_REQUIRES_CAPTURE !== $paymentIntent->status) {
            return;
        }

        $captureRequest = new CapturePaymentIntent($paymentIntent->id);
        $this->gateway->execute($captureRequest);

        /** @var PaymentIntent $paymentIntent */
        $paymentIntent = $captureRequest->getApiResource();
        /** @var ArrayObject $model */
        $model = $request->getModel();
        $model->exchangeArray($paymentIntent->toArray());
    }

    public function supports($request): bool
    {
        if (false === $request instanceof CaptureAuthorized) {
            return false;
        }

        return $request->getModel() instanceof ArrayObject;
    }
}
