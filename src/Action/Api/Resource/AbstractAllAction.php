<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Action\Api\StripeApiAwareTrait;
use FluxSE\PayumStripe\Request\Api\Resource\AllInterface;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Stripe\ApiOperations\All;
use Stripe\Collection;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;

abstract class AbstractAllAction implements AllResourceActionInterface
{
    use StripeApiAwareTrait,
        ResourceAwareActionTrait;

    /**
     * {@inheritDoc}
     *
     * @param AllInterface $request
     *
     * @throws ApiErrorException
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $this->checkRequest($request);

        $apiResources = $this->allApiResource($request);

        $request->setApiResources($apiResources);
    }

    /**
     * {@inheritDoc}
     *
     * @throws ApiErrorException
     */
    public function allApiResource(AllInterface $request): Collection
    {
        $apiResourceClass = $this->getApiResourceClass();
        if (false === method_exists($apiResourceClass, 'all')) {
            throw new LogicException(sprintf(
                'This class "%s" is not an instance of "%s"',
                $apiResourceClass,
                All::class
            ));
        }

        Stripe::setApiKey($this->api->getSecretKey());

        /** @see All::all() */
        return $apiResourceClass::all(
            $request->getParameters(),
            $request->getOptions()
        );
    }

    /**
     * {@inheritDoc}
     *
     * @param AllInterface $request
     */
    public function supports($request): bool
    {
        return
            $request instanceof AllInterface &&
            $this->supportAlso($request)
        ;
    }

    /**
     * @param AllInterface $request
     */
    protected function checkRequest(AllInterface $request): void
    {
        // Silent is golden
    }
}
