<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\StripeJs;

use ArrayObject;
use FluxSE\PayumStripe\Action\AbstractCaptureAction;
use FluxSE\PayumStripe\Request\Api\Resource\CreatePaymentIntent;
use FluxSE\PayumStripe\Request\CaptureAuthorized;
use FluxSE\PayumStripe\Request\StripeJs\Api\RenderStripeJs;
use Payum\Core\Request\Generic;
use Stripe\ApiResource;
use Stripe\PaymentIntent;

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
        $actionUrl = $token->getTargetUrl();

        $renderRequest = new RenderStripeJs($captureResource, $actionUrl);
        $this->gateway->execute($renderRequest);
    }

    protected function processNotNew(ArrayObject $model, Generic $request): void
    {
        parent::processNotNew($model, $request);

        $this->capturesIfPaymentIntentStatusCapture($model, $request);
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
