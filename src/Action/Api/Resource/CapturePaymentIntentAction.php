<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\CapturePaymentIntent;
use FluxSE\PayumStripe\Request\Api\Resource\CustomCallInterface;
use FluxSE\PayumStripe\Request\Api\Resource\RetrieveInterface;
use Stripe\ApiResource;
use Stripe\PaymentIntent;
use Stripe\Service\AbstractService;
use Stripe\StripeClient;

final class CapturePaymentIntentAction extends AbstractRetrieveAction
{
    public function getStripeService(StripeClient $stripeClient): AbstractService
    {
        return $stripeClient->paymentIntents;
    }

    public function supportAlso(RetrieveInterface $request): bool
    {
        return $request instanceof CapturePaymentIntent;
    }

    /**
     * @param CustomCallInterface&RetrieveInterface $request
     */
    public function retrieveApiResource(RetrieveInterface $request): ApiResource
    {
        /** @var PaymentIntent $apiResource */
        $apiResource = parent::retrieveApiResource($request);

        return $this->capturePaymentIntent($apiResource, $request);
    }

    public function capturePaymentIntent(PaymentIntent $apiResource, CustomCallInterface $request): PaymentIntent
    {
        return $apiResource->capture(
            $request->getCustomCallParameters(),
            $request->getCustomCallOptions()
        );
    }
}
