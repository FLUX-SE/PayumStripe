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

final class CancelPaymentIntentAction extends AbstractPaymentIntentAwareAction
{
    /** @var string[] */
    public const CANCELABLE_STATUS = [
        PaymentIntent::STATUS_REQUIRES_ACTION,
        PaymentIntent::STATUS_REQUIRES_CAPTURE,
        PaymentIntent::STATUS_REQUIRES_CONFIRMATION,
        PaymentIntent::STATUS_REQUIRES_PAYMENT_METHOD,
        // It only works with US bank account
        // @see https://stripe.com/docs/payments/intents
        PaymentIntent::STATUS_PROCESSING,
    ];

    /**
     * @param Cancel $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var ArrayObject $model */
        $model = $request->getModel();
        if (false === $this->isCancelable($model)) {
            return;
        }

        $paymentIntent = $this->preparePaymentIntent($request);
        if (null === $paymentIntent) {
            return;
        }

        $cancelRequest = new CancelPaymentIntent($paymentIntent->id);
        $this->gateway->execute($cancelRequest);

        /** @var PaymentIntent $paymentIntent */
        $paymentIntent = $cancelRequest->getApiResource();
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

        return PaymentIntent::OBJECT_NAME === $model->offsetGet('object');
    }

    private function isCancelable(ArrayObject $model): bool
    {
        return in_array($model->offsetGet('status'), $this::CANCELABLE_STATUS, true);
    }
}
