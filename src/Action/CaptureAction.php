<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action;

use ArrayAccess;
use FluxSE\PayumStripe\Request\Api\RedirectToCheckout;
use FluxSE\PayumStripe\Request\Api\Resource\CreateSession;
use LogicException;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Sync;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Payum\Core\Security\TokenInterface;

class CaptureAction implements ActionInterface, GatewayAwareInterface, GenericTokenFactoryAwareInterface
{
    use GatewayAwareTrait;
    use GenericTokenFactoryAwareTrait;

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);
        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (false === $model->offsetExists('id')) {
            // 0. Create another token to allow payment webhooks to use `Notify`
            $token = $this->getRequestToken($request);
            $notifyToken = $this->tokenFactory->createNotifyToken(
                $token->getGatewayName(),
                $token->getDetails()
            );
            $this->embedNotifyTokenHash($model, $notifyToken);

            // 1. Use the capture URL to `Sync` the payment
            //    after the customer get back from Stripe Checkout Session
            $model->offsetSet('success_url', $token->getTargetUrl());
            $model->offsetSet('cancel_url', $token->getTargetUrl());

            // 2. Create a new `Session`
            $createCheckoutSession = new CreateSession($model->getArrayCopy());
            $this->gateway->execute($createCheckoutSession);
            $session = $createCheckoutSession->getApiResource();

            // 3. Prepare storing of a `Session` object synced to one of this object :
            //      - `PaymentIntent`
            //      - `SetupIntent`
            //      - `Subscription`
            //      - `Session`
            //    (legacy Stripe payments were storing `Charge` object)
            $model->exchangeArray($session->toArray());
            $this->gateway->execute(new Sync($model));

            // 4. Display the page to redirect to Stripe Checkout portal
            $redirectToCheckout = new RedirectToCheckout($session->toArray());
            $this->gateway->execute($redirectToCheckout);
            // Nothing else will be execute after this line because of the rendering of the template
        }

        // 0. Retrieve `PaymentIntent` object and update it
        $this->gateway->execute(new Sync($model));
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
    public function embedNotifyTokenHash(ArrayObject $model, TokenInterface $token): void
    {
        $metadata = $model->offsetGet('metadata');
        if (null === $metadata) {
            $metadata = [];
        }

        $metadata['token_hash'] = $token->getHash();
        $model['metadata'] = $metadata;

        $modeDataKey = $this->detectModeData($model);
        $this->embedOnModeData($model, $token, $modeDataKey);
    }

    public function supports($request): bool
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof ArrayAccess
        ;
    }

    public function embedOnModeData(ArrayObject $model, TokenInterface $token, string $modeDataKey): void
    {
        $paymentIntentData = $model->offsetGet($modeDataKey);
        if (null === $paymentIntentData) {
            $paymentIntentData = [];
        }
        if (false === isset($paymentIntentData['metadata'])) {
            $paymentIntentData['metadata'] = [];
        }
        $paymentIntentData['metadata']['token_hash'] = $token->getHash();
        $model[$modeDataKey] = $paymentIntentData;
    }

    protected function detectModeData(ArrayObject $model): string
    {
        if ($model->offsetExists('subscription_data')) {
            return 'subscription_data';
        }

        if ($model->offsetExists('setup_intent_data')) {
            return 'setup_intent_data';
        }

        return 'payment_intent_data';
    }

    private function getRequestToken(Capture $request): TokenInterface
    {
        $token = $request->getToken();

        if (null === $token) {
            throw new LogicException('The request token should not be null !');
        }

        return $token;
    }
}
