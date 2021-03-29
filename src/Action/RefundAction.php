<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action;

use ArrayObject;
use FluxSE\PayumStripe\Request\Api\Resource\CreateRefund;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Refund;
use Stripe\PaymentIntent;
use Stripe\Refund as StripeRefund;

final class RefundAction extends AbstractPaymentIntentAwareAction
{
    /**
     * @param Refund $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var ArrayObject $model */
        $model = $request->getModel();

        $object = $model['object'] ?? null;
        if (PaymentIntent::OBJECT_NAME !== $object) {
            return;
        }

        $id = $model['id'] ?? null;
        if (empty($id)) {
            return;
        }

        $refundModel = new ArrayObject([
            'payment_intent' => $id,
        ]);
        $this->embedNotifyTokenHash($refundModel, $request);

        $refundRequest = new CreateRefund($refundModel->getArrayCopy());
        $this->gateway->execute($refundRequest);

        /** @var StripeRefund $refund */
        $refund = $refundRequest->getApiResource();
        $model->exchangeArray($refund->toArray());
    }

    /**
     * The token hash will be stored to a different
     * metadata key to avoid consuming the default one.
     */
    public function getTokenHashMetadataKeyName(): string
    {
        return TokenHashKeysInterface::REFUND_TOKEN_HASH_KEY_NAME;
    }

    public function supports($request): bool
    {
        if (false === $request instanceof Refund) {
            return false;
        }

        return $request->getModel() instanceof ArrayObject;
    }
}
