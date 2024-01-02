<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action;

use ArrayAccess;
use FluxSE\PayumStripe\Request\Api\Resource\CancelPaymentIntent;
use FluxSE\PayumStripe\Token\TokenHashKeysInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
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

        if (PaymentIntent::STATUS_CANCELED === $paymentIntent->status) {
            return;
        }

        $cancelRequest = new CancelPaymentIntent($paymentIntent->id);
        $this->gateway->execute($cancelRequest);

        /** @var PaymentIntent $paymentIntent */
        $paymentIntent = $cancelRequest->getApiResource();
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
        return TokenHashKeysInterface::CANCEL_TOKEN_HASH_KEY_NAME;
    }

    public function supports($request): bool
    {
        if (false === $request instanceof Cancel) {
            return false;
        }

        $model = $request->getModel();
        if (!$model instanceof ArrayAccess) {
            return false;
        }

        // if capture_method=manual it means the payment intent was created from a checkout session with authorization
        return $model->offsetExists('capture_method') && $model->offsetGet('capture_method') === 'manual';
    }
}
