<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\CancelSubscription;
use FluxSE\PayumStripe\Request\Api\Resource\DeleteInterface;
use Payum\Core\Exception\LogicException;
use Stripe\ApiOperations\Retrieve;
use Stripe\ApiResource;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;
use Stripe\Subscription;

final class CancelSubscriptionAction extends AbstractDeleteAction
{
    protected $apiResourceClass = Subscription::class;

    public function supportAlso(DeleteInterface $request): bool
    {
        return $request instanceof CancelSubscription;
    }

    /**
     * @throws ApiErrorException
     */
    public function deleteApiResource(DeleteInterface $request): ApiResource
    {
        $apiResourceClass = $this->getApiResourceClass();
        if (false === method_exists($apiResourceClass, 'retrieve')) {
            throw new LogicException(sprintf(
                'This class "%s" is not an instance of "%s"',
                (string) $apiResourceClass,
                Retrieve::class
            ));
        }

        Stripe::setApiKey($this->api->getSecretKey());

        /** @see Retrieve::retrieve() */
        $apiResource = $apiResourceClass::retrieve(
            $request->getId(),
            $request->getOptions()
        );

        if (false === $apiResource instanceof Subscription) {
            throw new LogicException(sprintf(
                'This class "%s" is not an instance of "%s"',
                $apiResourceClass,
                Subscription::class
            ));
        }

        $apiResource->cancel();

        return $apiResource;
    }
}
