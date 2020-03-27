<?php

declare(strict_types=1);

namespace Prometee\PayumStripeCheckoutSession\Action;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Sync;
use Prometee\PayumStripeCheckoutSession\Request\Api\Resource\RetrievePaymentIntent;
use Stripe\Checkout\Session;
use Stripe\PaymentIntent;

class SyncAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritDoc}
     *
     * @param Sync $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);
        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (empty($model['id'])) {
            return;
        }
        if (empty($model['object'])) {
            return;
        }

        $paymentIntentId = null;
        $objectName = $model->offsetGet('object');
        if (PaymentIntent::OBJECT_NAME === $objectName) {
            $paymentIntentId = $model->offsetGet('id');
        }
        if (Session::OBJECT_NAME === $objectName) {
            $paymentIntentId = $model->offsetGet('payment_intent');
        }

        if (null === $paymentIntentId) {
            return;
        }

        $retrievePaymentIntent = new RetrievePaymentIntent($paymentIntentId);
        $this->gateway->execute($retrievePaymentIntent);
        /** @var PaymentIntent $paymentIntent */
        $paymentIntent = $retrievePaymentIntent->getModel();
        $model->replace($paymentIntent->toArray());
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Sync &&
            $request->getModel() instanceof ArrayAccess
            ;
    }
}
