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
use Payum\Core\Security\TokenInterface;
use Prometee\PayumStripeCheckoutSession\Request\Api\RedirectToCheckout;
use Prometee\PayumStripeCheckoutSession\Request\Api\Resource\CreateSession;

class CaptureAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritdoc}
     *
     * @param Capture $request
     */
    public function execute($request): void
    {
        /* @var $request Capture */
        RequestNotSupportedException::assertSupports($this, $request);
        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (false === $model->offsetExists('id')) {
            $token = $request->getToken();
            // 0. Use the `afterToken->getTargetUrl()` url instead of `$token->getTargetUrl()`
            // Two tokens are generally made :
            //   - one is the current `$token`
            //   - and a second one which give its `$afterToken->getTargetUrl()`
            //     to the current `$token` filled as the `afterUrl` attribute.
            // So the customer will consume the `$afterToken` while webhooks will
            // consume the current `$token`
            $model['success_url'] = $token->getAfterUrl();
            $model['cancel_url'] = $token->getAfterUrl();
            $this->embedTokenHash($model, $token);

            // 1. Create a new `Session`
            $createCheckoutSession = new CreateSession($model);
            $this->gateway->execute($createCheckoutSession);
            $session = $createCheckoutSession->getApiResource();
            if (null === $session) {
                throw new LogicException('The event wrapper should not be null !');
            }

            // 2. Prepare storing of an `PaymentIntent` object
            //    (legacy Stripe payments were storing `Charge` object)
            $model->exchangeArray($session->toArray());
            $this->gateway->execute(new Sync($model));

            // 3. Display the page to redirect to Stripe Checkout portal
            $redirectToCheckout = new RedirectToCheckout($session->toArray());
            $this->gateway->execute($redirectToCheckout);
            // Nothing else will be execute after this line because of the rendering of the template
        }

        // 0. Retrieve `PaymentIntent` object and update it
        $this->gateway->execute(new Sync($model));
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
    public function embedTokenHash(ArrayObject $model, TokenInterface $token): void
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
}
