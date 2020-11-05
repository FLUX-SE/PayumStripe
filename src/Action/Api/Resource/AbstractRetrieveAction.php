<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Action\Api\StripeApiAwareTrait;
use FluxSE\PayumStripe\Request\Api\Resource\RetrieveInterface;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Stripe\ApiOperations\Retrieve;
use Stripe\ApiResource;
use Stripe\Stripe;

abstract class AbstractRetrieveAction implements RetrieveResourceActionInterface
{
    use StripeApiAwareTrait;
    use ResourceAwareActionTrait;

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var RetrieveInterface $request */
        $apiResource = $this->retrieveApiResource($request);

        $request->setApiResource($apiResource);
    }

    public function retrieveApiResource(RetrieveInterface $request): ApiResource
    {
        $apiResourceClass = $this->getApiResourceClass();
        if (false === method_exists($apiResourceClass, 'retrieve')) {
            throw new LogicException(sprintf('This class "%s" is not an instance of "%s" !', $apiResourceClass, Retrieve::class));
        }

        Stripe::setApiKey($this->api->getSecretKey());

        /* @see Retrieve::retrieve() */
        return $apiResourceClass::retrieve(
            $request->getId(),
            $request->getOptions()
        );
    }

    public function supports($request): bool
    {
        return
            $request instanceof RetrieveInterface &&
            $this->supportAlso($request)
        ;
    }
}
