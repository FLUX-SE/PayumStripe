<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\CustomCallInterface;
use FluxSE\PayumStripe\Request\Api\Resource\ExpireSession;
use FluxSE\PayumStripe\Request\Api\Resource\RetrieveInterface;
use Stripe\ApiResource;
use Stripe\Checkout\Session;
use Stripe\Service\AbstractService;
use Stripe\StripeClient;

final class ExpireSessionAction extends AbstractRetrieveAction
{
    public function getStripeService(StripeClient $stripeClient): AbstractService
    {
        return $stripeClient->checkout->sessions;
    }

    public function supportAlso(RetrieveInterface $request): bool
    {
        return $request instanceof ExpireSession;
    }

    /**
     * @param CustomCallInterface&RetrieveInterface $request
     */
    public function retrieveApiResource(RetrieveInterface $request): ApiResource
    {
        /** @var Session $apiResource */
        $apiResource = parent::retrieveApiResource($request);

        return $this->expireSession($apiResource, $request);
    }

    public function expireSession(Session $apiResource, CustomCallInterface $request): Session
    {
        return $apiResource->expire(
            $request->getCustomCallParameters(),
            $request->getCustomCallOptions()
        );
    }
}
