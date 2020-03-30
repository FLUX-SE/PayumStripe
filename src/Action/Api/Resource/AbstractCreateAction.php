<?php

declare(strict_types=1);

namespace Prometee\PayumStripeCheckoutSession\Action\Api\Resource;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Prometee\PayumStripeCheckoutSession\Action\Api\StripeApiAwareTrait;
use Prometee\PayumStripeCheckoutSession\Request\Api\Resource\CreateInterface;
use Stripe\ApiOperations\Create;
use Stripe\ApiResource;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;

/**
 * @method string|Create getApiResourceClass() : string
 */
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
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $this->checkRequest($request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

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
        Stripe::setApiKey($this->api->getSecretKey());

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
