<?php

declare(strict_types=1);

namespace Prometee\PayumStripeCheckoutSession\Action;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
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
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);
        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (empty($model['object'])) {
            return;
        }

        $paymentIntentId = null;
        $objectName = (string) $model->offsetGet('object');
        $paymentIntentId = $this->findPaymentIntentId($objectName, $model);

        if (null === $paymentIntentId) {
            return;
        }

        $retrievePaymentIntent = new RetrievePaymentIntent($paymentIntentId);
        $this->gateway->execute($retrievePaymentIntent);
        $paymentIntent = $retrievePaymentIntent->getApiResource();

        if (null === $paymentIntent) {
            throw new LogicException('The payment intent should not be null !');
        }

        $model->exchangeArray($paymentIntent->toArray());
    }

    /**
     * {@inheritDoc}
     *
     * @param Sync $request
     */
    public function supports($request): bool
    {
        return
            $request instanceof Sync &&
            $request->getModel() instanceof ArrayAccess
            ;
    }

    /**
     * @param string $objectName
     * @param ArrayObject $model
     *
     * @return string|null
     */
    protected function findPaymentIntentId(string $objectName, ArrayObject $model): ?string
    {
        if (PaymentIntent::OBJECT_NAME === $objectName) {
            if (false === $model->offsetExists('id')) {
                return null;
            }
            return (string) $model->offsetGet('id');
        }
        if (Session::OBJECT_NAME === $objectName) {
            if (false === $model->offsetExists('payment_intent')) {
                return null;
            }
            return (string) $model->offsetGet('payment_intent');
        }

        return null;
    }
}
