<?php

declare(strict_types=1);

namespace Prometee\PayumStripeCheckoutSession\Action\Api\Resource;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Prometee\PayumStripeCheckoutSession\Action\Api\StripeApiAwareTrait;
use Prometee\PayumStripeCheckoutSession\Request\Api\Resource\CreateInterface;
use Stripe\ApiResource;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;

abstract class AbstractCreateAction implements CreateResourceActionInterface
{
    use StripeApiAwareTrait;

    /**
     * {@inheritDoc}
     *
     * @param CreateInterface $request
     *
     * @throws ApiErrorException
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $this->checkRequest($request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        Stripe::setApiKey($this->api->getSecretKey());
        $apiResource = $this->createApiResource($model, $request->getOptions());
        $request->setApiResource($apiResource);
    }

    /**
     * {@inheritDoc}
     *
     * @throws ApiErrorException
     */
    public function createApiResource(ArrayObject $model, ?array $options = null): ApiResource
    {
        /** @var ApiResource $apiResource */
        $apiResource = $this->getApiResourceClass()::create(
            $model->toUnsafeArrayWithoutLocal(),
            $options
        );

        return $apiResource;
    }

    /**
     * {@inheritDoc}
     *
     * @param CreateInterface $request
     */
    public function supports($request)
    {
        return
            $request instanceof CreateInterface &&
            $this->supportAlso($request)
        ;
    }

    /**
     * @param CreateInterface $request
     */
    protected function checkRequest(CreateInterface $request)
    {
        // Silent is golden
    }
}
