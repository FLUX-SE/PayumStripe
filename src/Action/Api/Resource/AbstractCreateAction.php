<?php

declare(strict_types=1);

namespace Prometee\PayumStripeCheckoutSession\Action\Api\Resource;

use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Prometee\PayumStripeCheckoutSession\Action\Api\StripeApiAwareTrait;
use Prometee\PayumStripeCheckoutSession\Request\Api\Resource\CreateInterface;
use Stripe\ApiOperations\Create;
use Stripe\ApiResource;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;

abstract class AbstractCreateAction implements CreateResourceActionInterface
{
    use StripeApiAwareTrait,
        ResourceAwareActionTrait;

    /**
     * {@inheritDoc}
     *
     * @param CreateInterface $request
     *
     * @throws ApiErrorException
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $this->checkRequest($request);

        $apiResource = $this->createApiResource($request);

        $request->setApiResource($apiResource);
    }

    /**
     * {@inheritDoc}
     *
     * @throws ApiErrorException
     */
    public function createApiResource(CreateInterface $request): ApiResource
    {
        $apiResourceClass = $this->getApiResourceClass();
        if (false === method_exists($apiResourceClass, 'create')) {
            throw new LogicException(sprintf(
                'This class "%s" is not an instance of "%s"',
                (string) $apiResourceClass,
                Create::class
            ));
        }

        Stripe::setApiKey($this->api->getSecretKey());

        /** @see Create::create() */
        /** @var ApiResource $apiResource */
        $apiResource = $apiResourceClass::create(
            $request->getParameters(),
            $request->getOptions()
        );

        return $apiResource;
    }

    /**
     * {@inheritDoc}
     *
     * @param CreateInterface $request
     */
    public function supports($request): bool
    {
        return
            $request instanceof CreateInterface &&
            $this->supportAlso($request)
        ;
    }

    /**
     * @param CreateInterface $request
     */
    protected function checkRequest(CreateInterface $request): void
    {
        // Silent is golden
    }
}
