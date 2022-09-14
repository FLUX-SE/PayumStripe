<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Action\Api\StripeApiAwareTrait;
use FluxSE\PayumStripe\Request\Api\Resource\DeleteInterface;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Stripe\ApiOperations\Delete;
use Stripe\ApiOperations\Retrieve;
use Stripe\ApiResource;
use Stripe\Stripe;

abstract class AbstractDeleteAction implements DeleteResourceActionInterface
{
    use StripeApiAwareTrait;
    use ResourceAwareActionTrait;

    /**
     * @param DeleteInterface $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $apiResource = $this->deleteApiResource($request);

        $request->setApiResource($apiResource);
    }

    public function deleteApiResource(DeleteInterface $request): ApiResource
    {
        $apiResourceClass = $this->getApiResourceClass();
        if (false === method_exists($apiResourceClass, 'retrieve')) {
            throw new LogicException(sprintf('This class "%s" is not an instance of "%s" !', $apiResourceClass, Retrieve::class));
        }

        if (false === method_exists($apiResourceClass, 'delete')) {
            throw new LogicException(sprintf('This class "%s" is not an instance of "%s" !', $apiResourceClass, Delete::class));
        }

        Stripe::setApiKey($this->api->getSecretKey());

        /** @see Retrieve::retrieve() */
        $apiResource = $apiResourceClass::retrieve(
            $request->getId(),
            $request->getOptions()
        );

        /* @see Delete::delete() */
        return $apiResource->delete();
    }

    public function supports($request): bool
    {
        return
            $request instanceof DeleteInterface &&
            $this->supportAlso($request)
        ;
    }
}
