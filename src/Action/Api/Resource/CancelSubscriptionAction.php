<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\AbstractCustomCall;
use FluxSE\PayumStripe\Request\Api\Resource\CancelSubscription;
use FluxSE\PayumStripe\Request\Api\Resource\RetrieveInterface;
use Stripe\ApiResource;
use Stripe\Subscription;

final class CancelSubscriptionAction extends AbstractRetrieveAction
{
    protected $apiResourceClass = Subscription::class;

    public function supportAlso(RetrieveInterface $request): bool
    {
        return $request instanceof CancelSubscription;
    }

    /**
     * @param AbstractCustomCall&RetrieveInterface $request
     */
    public function retrieveApiResource(RetrieveInterface $request): ApiResource
    {
        /** @var Subscription $apiResource */
        $apiResource = parent::retrieveApiResource($request);

        $apiResource->cancel(
            $request->getCustomCallParameters(),
            $request->getCustomCallOptions()
        );

        return $apiResource;
    }
}
