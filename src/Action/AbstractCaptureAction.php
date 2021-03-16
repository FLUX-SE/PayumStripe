<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action;

use ArrayAccess;
use FluxSE\PayumStripe\Request\CaptureAuthorized;
use LogicException;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Generic;
use Payum\Core\Request\Sync;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Payum\Core\Security\TokenInterface;
use Stripe\ApiResource;

abstract class AbstractCaptureAction implements ActionInterface, GatewayAwareInterface, GenericTokenFactoryAwareInterface
{
    use GatewayAwareTrait;
    use GenericTokenFactoryAwareTrait;

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

            // 3. Prepare storing of a `Session` object synced to one of this object :
            //      - `PaymentIntent`
            //      - `SetupIntent`
            //      - `Subscription`
            //      - `Session`
            //    (legacy Stripe payments were storing `Charge` object)
            $model->exchangeArray($apiResource->toArray());
            $this->gateway->execute(new Sync($model));

            // 4. Display the page to redirect to Stripe Checkout portal
            $this->render($apiResource, $request);
            // Nothing else will be execute after this line because of the rendering of the template
        }

        // 0. Retrieve the `PaymentIntent`|`SetupIntent`|`Subscription` object and update it
        $this->gateway->execute(new Sync($model));

        // 1. Specific case of authorized payments being captured
        // If it isn't an authorized PaymentIntent then nothing is done
        $captureAuthorizedRequest = new CaptureAuthorized($this->getRequestToken($request));
        $captureAuthorizedRequest->setModel($model);
        $this->gateway->execute($captureAuthorizedRequest);
    }

    public function createNotifyToken(Generic $request): TokenInterface
    {
        $token = $this->getRequestToken($request);

        return $this->tokenFactory->createNotifyToken(
            $token->getGatewayName(),
            $token->getDetails()
        );
    }

    /**
     * Save the token hash for future webhook consuming retrieval.
     *
     *  - A `Session` can be completed.
     *  - or its `PaymentIntent` can be canceled.
     *  - or its `SetupIntent` can be canceled.
     *
     * So the token hash have to be stored both on `Session` metadata and other mode metadata
     */
    public function embedNotifyTokenHash(ArrayObject $model, Generic $request): TokenInterface
    {
        $metadata = $model->offsetGet('metadata');
        if (null === $metadata) {
            $metadata = [];
        }

        $notifyToken = $this->createNotifyToken($request);
        $metadata['token_hash'] = $notifyToken->getHash();
        $model['metadata'] = $metadata;

        return $notifyToken;
    }

    protected function getRequestToken(Generic $request): TokenInterface
    {
        $token = $request->getToken();

        if (null === $token) {
            throw new LogicException('The request token should not be null !');
        }

        return $token;
    }

    abstract protected function createApiResource(ArrayObject $model, Generic $request): ApiResource;

    abstract protected function render(ApiResource $captureResource, Generic $request): void;

    public function supports($request): bool
    {
        if (false === $request instanceof Capture) {
            return false;
        }

        return $request->getModel() instanceof ArrayAccess;
    }
}
