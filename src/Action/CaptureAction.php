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
use Payum\Core\Request\Capture;
use Payum\Core\Request\Sync;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Payum\Core\Security\TokenInterface;
use Prometee\PayumStripeCheckoutSession\Request\Api\RedirectToCheckout;
use Prometee\PayumStripeCheckoutSession\Request\Api\Resource\CreateSession;

class CaptureAction implements ActionInterface, GatewayAwareInterface, GenericTokenFactoryAwareInterface
{
    use GatewayAwareTrait,
        GenericTokenFactoryAwareTrait;

    /**
     * {@inheritdoc}
     *
     * @param Capture $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);
        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (false === $model->offsetExists('id')) {
            // 0. Create another token to allow payment webhooks to use `Notify`
            $token = $request->getToken();
            $notifyToken = $this->tokenFactory->createNotifyToken(
                $token->getGatewayName(),
                $model
            );
            $this->embedNotifyTokenHash($model, $notifyToken);

            // 1. Use the capture URL to `Sync` the payment
            //    after the customer get back from Stripe Checkout Session
            $model['success_url'] = $token->getTargetUrl();
            $model['cancel_url'] = $token->getTargetUrl();

            // 2. Create a new `Session`
            $createCheckoutSession = new CreateSession($model);
            $this->gateway->execute($createCheckoutSession);
            $session = $createCheckoutSession->getApiResource();
            if (null === $session) {
                throw new LogicException('The event wrapper should not be null !');
            }

            // 3. Prepare storing of an `PaymentIntent` object
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
     * Save the token hash for future webhook consuming retrieval
     *
     * comment : A `Session` can be completed or its `PaymentIntent` can be canceled.
     *           So the token hash have to be stored both on `Session` metadata and on
     *           `PaymentIntent` metadata
     *
     * @param ArrayObject $model
     * @param TokenInterface $token
     */
    public function embedNotifyTokenHash(ArrayObject $model, TokenInterface $token): void
    {
        $metadata = $model->offsetGet('metadata');
        if (null === $metadata) {
            $metadata = [];
        }

        $metadata['token_hash'] = $token->getHash();
        $model['metadata'] = $metadata;

        $paymentIntentData = $model->offsetGet('payment_intent_data');
        if (null === $paymentIntentData) {
            $paymentIntentData = [];
        }
        if (false === isset($paymentIntentData['metadata'])) {
            $paymentIntentData['metadata'] = [];
        }
        $paymentIntentData['metadata']['token_hash'] = $token->getHash();
        $model['payment_intent_data'] = $paymentIntentData;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request): bool
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof ArrayAccess
        ;
    }
}
