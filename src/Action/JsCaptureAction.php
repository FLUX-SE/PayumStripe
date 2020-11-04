<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action;

use FluxSE\PayumStripe\Request\Api\Pay;
use FluxSE\PayumStripe\Request\Api\Resource\CreatePaymentIntent;
use LogicException;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\Capture;
use Stripe\ApiResource;
use Stripe\PaymentIntent;

class JsCaptureAction extends CaptureAction
{
    protected function createCaptureResource(ArrayObject $model, Capture $request): ApiResource
    {
        $createPaymentIntent = new CreatePaymentIntent($model->getArrayCopy());
        $this->gateway->execute($createPaymentIntent);

        return $createPaymentIntent->getApiResource();
    }

    protected function renderCapture(ApiResource $captureResource, Capture $request): void
    {
        if (false === $captureResource instanceof PaymentIntent) {
            throw new LogicException(sprintf('The $captureResource should be a "%s" !', PaymentIntent::class));
        }

        $token = $this->getRequestToken($request);
        $actionUrl = $token->getTargetUrl();
        $pay = new Pay($captureResource, $actionUrl);
        $this->gateway->execute($pay);
    }
}
