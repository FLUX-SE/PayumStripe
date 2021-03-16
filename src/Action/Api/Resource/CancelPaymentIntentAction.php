<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\AbstractCustomCall;
use FluxSE\PayumStripe\Request\Api\Resource\CancelPaymentIntent;
use FluxSE\PayumStripe\Request\Api\Resource\RetrieveInterface;
use Stripe\ApiResource;
use Stripe\PaymentIntent;

final class CancelPaymentIntentAction extends AbstractRetrieveAction
{
    protected $apiResourceClass = PaymentIntent::class;

    public function supportAlso(RetrieveInterface $request): bool
    {
        return $request instanceof CancelPaymentIntent;
    }

    /**
     * @param AbstractCustomCall&RetrieveInterface $request
     */
    public function retrieveApiResource(RetrieveInterface $request): ApiResource
    {
        /** @var PaymentIntent $apiResource */
        $apiResource = parent::retrieveApiResource($request);

        $apiResource->cancel(
            $request->getCustomCallParameters(),
            $request->getCustomCallOptions()
        );

        return $apiResource;
    }
}
