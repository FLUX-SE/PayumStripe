<?php

declare(strict_types=1);

namespace Prometee\PayumStripeCheckoutSession\Action\Api\Resource;

use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Prometee\PayumStripeCheckoutSession\Action\Api\StripeApiAwareTrait;
use Prometee\PayumStripeCheckoutSession\Request\Api\Resource\DeleteInterface;
use Stripe\ApiOperations\Delete;
use Stripe\ApiOperations\Retrieve;
use Stripe\ApiResource;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;

/**
 * @method Delete|Retrieve|string getApiResourceClass() : string
 */
abstract class AbstractDeleteAction implements DeleteActionInterface
{
    use StripeApiAwareTrait,
        ResourceAwareActionTrait;

    /**
     * {@inheritDoc}
     *
     * @param DeleteInterface $request
     *
     * @throws ApiErrorException
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $this->checkRequest($request);

        $apiResource = $this->retrieveApiResource($request);

        $request->setApiResource($apiResource);
    }

    /**
     * {@inheritDoc}
     *
     * @throws ApiErrorException
     */
    public function retrieveApiResource(DeleteInterface $request): ApiResource
    {
        Stripe::setApiKey($this->api->getSecretKey());

        $apiResource = $this->getApiResourceClass()::retrieve(
            $request->getId(),
            $request->getOptions()
        );

        if (false === $apiResource instanceof Delete) {
            throw new LogicException(sprintf(
                'This class "%s" is not an instance of "%s"',
                $this->getApiResourceClass(),
                Delete::class
            ));
        }

        /** @var ApiResource&Delete $apiResource */
        $apiResource->delete();

        return $apiResource;
    }

    /**
     * {@inheritDoc}
     *
     * @param DeleteInterface $request
     */
    public function supports($request): bool
    {
        return
            $request instanceof DeleteInterface &&
            $this->supportAlso($request)
        ;
    }

    /**
     * @param DeleteInterface $request
     */
    protected function checkRequest(DeleteInterface $request): void
    {
        // Silent is golden
    }
}
