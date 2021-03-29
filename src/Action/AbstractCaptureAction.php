<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action;

use ArrayAccess;
use ArrayObject as BaseArrayObject;
use FluxSE\PayumStripe\Request\CaptureAuthorized;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Generic;
use Payum\Core\Request\Sync;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Stripe\ApiResource;

abstract class AbstractCaptureAction implements ActionInterface, GatewayAwareInterface, GenericTokenFactoryAwareInterface
{
    use GatewayAwareTrait;
    use EmbeddableTokenTrait;

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (false === $model->offsetExists('id')) {
            // 0. Create another token to allow payment webhooks to use `Notify`
            $this->embedNotifyTokenHash($model, $request);

            // 1. Use the capture URL to `Sync` the payment
            //    after the customer get back from Stripe Checkout Session
            // 2. Create a new `Session`
            $apiResource = $this->createApiResource($model, $request);
            $model->exchangeArray($apiResource->toArray());

            // 3. [stripe_checkout_session] A `Session` object synced to one of those objects :
            //      - `PaymentIntent`
            //      - `SetupIntent`
            //      - `Subscription`
            //    (legacy Stripe payments were storing `Charge` object)
            $this->gateway->execute(new Sync($model));

            // 4. Display the page to redirect to Stripe Checkout portal
            $this->render($apiResource, $request);
        } else {
            // 0. Retrieve the `PaymentIntent`|`SetupIntent`|`Subscription` object and update it
            $this->gateway->execute(new Sync($model));

            // 1. Specific case of authorized payments being captured
            // If it isn't an authorized PaymentIntent then nothing is done
            $captureAuthorizedRequest = new CaptureAuthorized($this->getRequestToken($request));
            $captureAuthorizedRequest->setModel($model);
            $this->gateway->execute($captureAuthorizedRequest);
        }
    }

    abstract protected function createApiResource(BaseArrayObject $model, Generic $request): ApiResource;

    abstract protected function render(ApiResource $captureResource, Generic $request): void;

    public function supports($request): bool
    {
        if (false === $request instanceof Capture) {
            return false;
        }

        return $request->getModel() instanceof ArrayAccess;
    }
}
