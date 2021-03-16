<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\AbstractCustomCall;
use FluxSE\PayumStripe\Request\Api\Resource\CapturePaymentIntent;
use FluxSE\PayumStripe\Request\Api\Resource\RetrieveInterface;
use Stripe\ApiResource;
use Stripe\PaymentIntent;

class CapturePaymentIntentAction extends AbstractRetrieveAction
{
    protected $apiResourceClass = PaymentIntent::class;

    public function supportAlso(RetrieveInterface $request): bool
    {
        return $request instanceof CapturePaymentIntent;
    }

    /**
     * @param AbstractCustomCall&RetrieveInterface $request
     */
    public function retrieveApiResource(RetrieveInterface $request): ApiResource
    {
        /** @var PaymentIntent $apiResource */
        $apiResource = parent::retrieveApiResource($request);

        $apiResource->capture(
            $request->getCustomCallParameters(),
            $request->getCustomCallOptions()
        );

        return $apiResource;
    }
}
