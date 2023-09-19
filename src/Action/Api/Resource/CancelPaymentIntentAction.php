<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\CancelPaymentIntent;
use FluxSE\PayumStripe\Request\Api\Resource\CustomCallInterface;
use FluxSE\PayumStripe\Request\Api\Resource\RetrieveInterface;
use Stripe\ApiResource;
use Stripe\PaymentIntent;
use Stripe\Service\AbstractService;
use Stripe\StripeClient;

final class CancelPaymentIntentAction extends AbstractRetrieveAction
{
    public function getStripeService(StripeClient $stripeClient): AbstractService
    {
        return $stripeClient->paymentIntents;
    }

    public function supportAlso(RetrieveInterface $request): bool
    {
        return $request instanceof CancelPaymentIntent;
    }

    /**
     * @param CustomCallInterface&RetrieveInterface $request
     */
    public function retrieveApiResource(RetrieveInterface $request): ApiResource
    {
        /** @var PaymentIntent $apiResource */
        $apiResource = parent::retrieveApiResource($request);

        return $this->cancelPaymentIntent($apiResource, $request);
    }

    public function cancelPaymentIntent(PaymentIntent $apiResource, CustomCallInterface $request): PaymentIntent
    {
        return $apiResource->cancel(
            $request->getCustomCallParameters(),
            $request->getCustomCallOptions()
        );
    }
}
