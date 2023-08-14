<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action;

use ArrayObject;
use FluxSE\PayumStripe\Request\Api\Resource\CapturePaymentIntent;
use FluxSE\PayumStripe\Request\CaptureAuthorized;
use FluxSE\PayumStripe\Token\TokenHashKeysInterface;
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

        /** @var PaymentIntent $paymentIntent can't be null here already checked by the supports method */
        $paymentIntent = $this->preparePaymentIntent($request);

        $captureRequest = new CapturePaymentIntent($paymentIntent->id);
        $this->gateway->execute($captureRequest);

        /** @var PaymentIntent $paymentIntent */
        $paymentIntent = $captureRequest->getApiResource();
        /** @var ArrayObject $model */
        $model = $request->getModel();
        $model->exchangeArray($paymentIntent->toArray());
    }

    /**
     * The token hash will be stored to a different
     * metadata key to avoid consuming the default one.
     */
    public function getTokenHashMetadataKeyName(): string
    {
        return TokenHashKeysInterface::CAPTURE_AUTHORIZE_TOKEN_HASH_KEY_NAME;
    }

    public function supports($request): bool
    {
        if (false === $request instanceof CaptureAuthorized) {
            return false;
        }

        $model = $request->getModel();
        if (false === $model instanceof ArrayObject) {
            return false;
        }

        if (PaymentIntent::OBJECT_NAME !== $model->offsetGet('object')) {
            return false;
        }

        return PaymentIntent::STATUS_REQUIRES_CAPTURE === $model->offsetGet('status');
    }
}
