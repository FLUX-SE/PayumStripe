<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Action\Api\StripeApiAwareTrait;
use FluxSE\PayumStripe\Request\Api\Resource\CreateInterface;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Stripe\ApiOperations\Create;
use Stripe\ApiResource;
use Stripe\Stripe;

abstract class AbstractCreateAction implements CreateResourceActionInterface
{
    use StripeApiAwareTrait;
    use ResourceAwareActionTrait;

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $apiResource = $this->createApiResource($request);

        $request->setApiResource($apiResource);
    }

    /**
     * @throws LogicException
     */
    public function createApiResource(CreateInterface $request): ApiResource
    {
        $apiResourceClass = $this->getApiResourceClass();
        if (false === method_exists($apiResourceClass, 'create')) {
            throw new LogicException(sprintf('This class "%s" is not an instance of "%s" !', $apiResourceClass, Create::class));
        }

        Stripe::setApiKey($this->api->getSecretKey());

        /* @see Create::create() */
        return $apiResourceClass::create(
            $request->getParameters(),
            $request->getOptions()
        );
    }

    public function supports($request): bool
    {
        return
            $request instanceof CreateInterface &&
            $this->supportAlso($request)
        ;
    }
}
