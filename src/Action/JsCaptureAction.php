<?php

declare(strict_types=1);

namespace Prometee\PayumStripe\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Sync;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Prometee\PayumStripe\Request\Api\Pay;
use Prometee\PayumStripe\Request\Api\Resource\CreatePaymentIntent;

class JsCaptureAction extends CaptureAction implements ActionInterface, GatewayAwareInterface, GenericTokenFactoryAwareInterface
{
    use GatewayAwareTrait;
    use GenericTokenFactoryAwareTrait;

    /**
     * {@inheritdoc}
     *
     * @param Capture $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        // If we don't have the PaymentIntent object, we need to create it
        if (false === $model->offsetExists('id')) {
            // Create another token to allow payment webhooks to use `Notify`
            $token = $request->getToken();
            $notifyToken = $this->tokenFactory->createNotifyToken(
                $token->getGatewayName(),
                $token->getDetails()
            );
            $this->embedNotifyTokenHash($model, $notifyToken);

            // Create the PaymentIntent for this payment
            $createPaymentIntent = new CreatePaymentIntent($model->getArrayCopy());
            $this->gateway->execute($createPaymentIntent);
            $intent = $createPaymentIntent->getApiResource();
            if (null === $intent) {
                throw new LogicException('The event wrapper should not be null !');
            }

            // Prepare storing of an `PaymentIntent` object
            //   (legacy Stripe payments were storing `Charge` object)
            $model->exchangeArray($intent->toArray());
        }

        // Sync the PaymentIntent in order to get it updated
        $this->gateway->execute(new Sync($model));

        // If the PaymentIntent is paid we don't need to do anything more
        if ($model->offsetExists('status') && 'succeeded' === $model['status']) {
            return;
        }

        // Pay with the PaymentIntent model
        $pay = new Pay($request->getFirstModel(), $model);
        $this->gateway->execute($pay);
    }
}
