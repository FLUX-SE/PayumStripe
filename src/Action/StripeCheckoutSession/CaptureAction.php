<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\StripeCheckoutSession;

use ArrayObject;
use FluxSE\PayumStripe\Action\AbstractCaptureAction;
use FluxSE\PayumStripe\Request\Api\Resource\CreateSession;
use FluxSE\PayumStripe\Request\CaptureAuthorized;
use FluxSE\PayumStripe\Request\StripeCheckoutSession\Api\RedirectToCheckout;
use Payum\Core\Request\Cancel;
use Payum\Core\Request\Generic;
use Payum\Core\Request\Sync;
use Payum\Core\Security\TokenInterface;
use Stripe\ApiResource;
use Stripe\Checkout\Session;
use Stripe\PaymentIntent;

class CaptureAction extends AbstractCaptureAction
{
    protected function createApiResource(ArrayObject $model, Generic $request): ApiResource
    {
        $token = $this->getRequestToken($request);
        $model->offsetSet('success_url', $token->getTargetUrl());
        $model->offsetSet('cancel_url', $token->getTargetUrl());

        $createRequest = new CreateSession($model->getArrayCopy());
        $this->gateway->execute($createRequest);

        return $createRequest->getApiResource();
    }

    public function embedNotifyTokenHash(ArrayObject $model, Generic $request): TokenInterface
    {
        $notifyToken = parent::embedNotifyTokenHash($model, $request);

        $modeDataKey = $this->detectModeData($model);
        $this->embedOnModeData($model, $notifyToken, $modeDataKey);

        return $notifyToken;
    }

    public function embedOnModeData(ArrayObject $model, TokenInterface $token, string $modeDataKey): void
    {
        /** @var array|null $embeddedModeData */
        $embeddedModeData = $model->offsetGet($modeDataKey);
        if (null === $embeddedModeData) {
            $embeddedModeData = [];
        }
        if (false === isset($embeddedModeData['metadata'])) {
            $embeddedModeData['metadata'] = [];
        }
        $tokenHashMetadataKeyName = $this->getTokenHashMetadataKeyName();
        $embeddedModeData['metadata'][$tokenHashMetadataKeyName] = $token->getHash();
        $model->offsetSet($modeDataKey, $embeddedModeData);
    }

    protected function detectModeData(ArrayObject $model): string
    {
        $mode = Session::MODE_PAYMENT;
        if ($model->offsetExists('mode')) {
            $mode = $model->offsetGet('mode');
        }

        if ($model->offsetExists('subscription_data')) {
            return 'subscription_data';
        }

        if (Session::MODE_SUBSCRIPTION === $mode) {
            return 'subscription_data';
        }

        if ($model->offsetExists('setup_intent_data')) {
            return 'setup_intent_data';
        }

        if (Session::MODE_SETUP === $mode) {
            return 'setup_intent_data';
        }

        return 'payment_intent_data';
    }

    protected function render(ApiResource $captureResource, Generic $request): void
    {
        $redirectToCheckout = new RedirectToCheckout($captureResource->toArray());
        $this->gateway->execute($redirectToCheckout);
    }

    protected function processNotNew(ArrayObject $model, Generic $request): void
    {
        parent::processNotNew($model, $request);

        $this->cancelCheckoutSession($model);

        $this->capturesIfPaymentIntentStatusCapture($model, $request);
    }

    protected function cancelCheckoutSession(ArrayObject $model): void
    {
        // At this specific moment we are coming back from a CheckoutSession
        // We can then Make the Checkout Session expires
        $this->gateway->execute(new Cancel($model));
    }

    protected function capturesIfPaymentIntentStatusCapture(ArrayObject $model, Generic $request): void
    {
        if (
            PaymentIntent::OBJECT_NAME === $model->offsetGet('object')
            && PaymentIntent::STATUS_REQUIRES_CAPTURE === $model->offsetGet('status')
        ) {
            // Specific case of authorized payments being captured
            $captureAuthorizedRequest = new CaptureAuthorized($this->getRequestToken($request));
            $captureAuthorizedRequest->setModel($model);
            $this->gateway->execute($captureAuthorizedRequest);
        }
    }
}
