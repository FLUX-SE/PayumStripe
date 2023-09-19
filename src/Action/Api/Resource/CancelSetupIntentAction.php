<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\CancelSetupIntent;
use FluxSE\PayumStripe\Request\Api\Resource\CustomCallInterface;
use FluxSE\PayumStripe\Request\Api\Resource\RetrieveInterface;
use Stripe\ApiResource;
use Stripe\Service\AbstractService;
use Stripe\SetupIntent;
use Stripe\StripeClient;

final class CancelSetupIntentAction extends AbstractRetrieveAction
{
    public function getStripeService(StripeClient $stripeClient): AbstractService
    {
        return $stripeClient->setupIntents;
    }

    public function supportAlso(RetrieveInterface $request): bool
    {
        return $request instanceof CancelSetupIntent;
    }

    /**
     * @param CustomCallInterface&RetrieveInterface $request
     */
    public function retrieveApiResource(RetrieveInterface $request): ApiResource
    {
        /** @var SetupIntent $apiResource */
        $apiResource = parent::retrieveApiResource($request);

        return $this->cancelSetupIntent($apiResource, $request);
    }

    public function cancelSetupIntent(SetupIntent $apiResource, CustomCallInterface $request): SetupIntent
    {
        return $apiResource->cancel(
            $request->getCustomCallParameters(),
            $request->getCustomCallOptions()
        );
    }
}
