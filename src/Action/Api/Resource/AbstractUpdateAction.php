<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Action\Api\StripeApiAwareTrait;
use FluxSE\PayumStripe\Request\Api\Resource\UpdateInterface;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Stripe\ApiOperations\Update;
use Stripe\ApiResource;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;

abstract class AbstractUpdateAction implements UpdateResourceActionInterface
{
    use StripeApiAwareTrait;
    use ResourceAwareActionTrait;

    /**
     * {@inheritdoc}
     *
     * @param UpdateInterface $request
     *
     * @throws ApiErrorException
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $this->checkRequest($request);

        $apiResources = $this->updateApiResource($request);

        $request->setApiResource($apiResources);
    }

    /**
     * {@inheritdoc}
     *
     * @throws ApiErrorException
     */
    public function updateApiResource(UpdateInterface $request): ApiResource
    {
        $apiResourceClass = $this->getApiResourceClass();
        if (false === method_exists($apiResourceClass, 'update')) {
            throw new LogicException(sprintf('This class "%s" is not an instance of "%s"', $apiResourceClass, Update::class));
        }

        Stripe::setApiKey($this->api->getSecretKey());

        /** @see Update::update() */
        /** @var ApiResource $apiResource */
        $apiResource = $apiResourceClass::update(
            $request->getId(),
            $request->getParameters(),
            $request->getOptions()
        );

        return $apiResource;
    }

    /**
     * {@inheritdoc}
     *
     * @param UpdateInterface $request
     */
    public function supports($request): bool
    {
        return
            $request instanceof UpdateInterface &&
            $this->supportAlso($request)
        ;
    }

    protected function checkRequest(UpdateInterface $request): void
    {
        // Silent is golden
    }
}
