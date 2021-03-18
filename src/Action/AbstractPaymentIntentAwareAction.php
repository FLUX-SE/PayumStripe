<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action;

use FluxSE\PayumStripe\Request\Api\Resource\UpdatePaymentIntent;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Generic;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Stripe\PaymentIntent;

abstract class AbstractPaymentIntentAwareAction implements ActionInterface, GatewayAwareInterface, GenericTokenFactoryAwareInterface
{
    use GatewayAwareTrait;
    use EmbeddableTokenTrait;

    public function preparePaymentIntent(Generic $request): ?PaymentIntent
    {
        $model = ArrayObject::ensureArrayObject($request->getModel());
        $object = $model['object'] ?? null;
        if (PaymentIntent::OBJECT_NAME !== $object) {
            return null;
        }

        $id = $model['id'] ?? null;
        if (empty($id)) {
            return null;
        }

        $updatedModel = new ArrayObject();
        $this->embedNotifyTokenHash($updatedModel, $request);

        $updateRequest = new UpdatePaymentIntent($id, $updatedModel->getArrayCopy());
        $this->gateway->execute($updateRequest);

        /** @var PaymentIntent $paymentIntent */
        $paymentIntent = $updateRequest->getApiResource();

        return $paymentIntent;
    }
}
