<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\StripeCheckoutSession;

use ArrayObject;
use FluxSE\PayumStripe\Action\AbstractCaptureAction;
use FluxSE\PayumStripe\Request\Api\Resource\CreateSession;
use FluxSE\PayumStripe\Request\StripeCheckoutSession\Api\RedirectToCheckout;
use Payum\Core\Request\Generic;
use Payum\Core\Security\TokenInterface;
use Stripe\ApiResource;

class CaptureAction extends AbstractCaptureAction
{
    protected function createApiResource(ArrayObject $model, Generic $request): ApiResource
    {
        $token = $this->getRequestToken($request);
        $model->offsetSet('success_url', $token->getAfterUrl());
        $model->offsetSet('cancel_url', $token->getAfterUrl());

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
        $mode = 'payment';
        if ($model->offsetExists('mode')) {
            $mode = $model->offsetGet('mode');
        }

        if ($model->offsetExists('subscription_data')) {
            return 'subscription_data';
        }

        if ('subscription' === $mode) {
            return 'subscription_data';
        }

        if ($model->offsetExists('setup_intent_data')) {
            return 'setup_intent_data';
        }

        if ('setup' === $mode) {
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
