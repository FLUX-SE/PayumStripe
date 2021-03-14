<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\StripeCheckoutSession;

use FluxSE\PayumStripe\Action\AbstractCaptureAction;
use FluxSE\PayumStripe\Request\Api\Resource\CreateSession;
use FluxSE\PayumStripe\Request\StripeCheckoutSession\Api\RedirectToCheckout;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\Generic;
use Payum\Core\Security\TokenInterface;
use Stripe\ApiResource;

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

    public function embedNotifyTokenHash(ArrayObject $model, TokenInterface $token): void
    {
        parent::embedNotifyTokenHash($model, $token);

        $modeDataKey = $this->detectModeData($model);
        $this->embedOnModeData($model, $token, $modeDataKey);
    }

    public function embedOnModeData(ArrayObject $model, TokenInterface $token, string $modeDataKey): void
    {
        $embeddedModeData = $model->offsetGet($modeDataKey);
        if (null === $embeddedModeData) {
            $embeddedModeData = [];
        }
        if (false === isset($embeddedModeData['metadata'])) {
            $embeddedModeData['metadata'] = [];
        }
        $embeddedModeData['metadata']['token_hash'] = $token->getHash();
        $model->offsetSet($modeDataKey, $embeddedModeData);
    }

    protected function detectModeData(ArrayObject $model): string
    {
        if ($model->offsetExists('subscription_data')) {
            return 'subscription_data';
        }

        if ('subscription' === $model->offsetGet('mode')) {
            return 'subscription_data';
        }

        if ($model->offsetExists('setup_intent_data')) {
            return 'setup_intent_data';
        }

        if ('setup' === $model->offsetGet('mode')) {
            return 'setup_intent_data';
        }

        return 'payment_intent_data';
    }

    protected function render(ApiResource $captureResource, Generic $request): void
    {
        $redirectToCheckout = new RedirectToCheckout($captureResource->toArray());
        $this->gateway->execute($redirectToCheckout);
    }
}
