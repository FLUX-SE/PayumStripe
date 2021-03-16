<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\StripeJs;

use FluxSE\PayumStripe\Action\AbstractCaptureAction;
use FluxSE\PayumStripe\Request\Api\Resource\CreatePaymentIntent;
use FluxSE\PayumStripe\Request\StripeJs\Api\RenderStripeJs;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\Generic;
use Stripe\ApiResource;

class CaptureAction extends AbstractCaptureAction
{
    protected function createApiResource(ArrayObject $model, Generic $request): ApiResource
    {
        $createRequest = new CreatePaymentIntent($model->getArrayCopy());
        $this->gateway->execute($createRequest);

        return $createRequest->getApiResource();
    }

    protected function render(ApiResource $captureResource, Generic $request): void
    {
        $token = $this->getRequestToken($request);
        $actionUrl = $token->getAfterUrl();

        $renderRequest = new RenderStripeJs($captureResource, $actionUrl);
        $this->gateway->execute($renderRequest);
    }
}
