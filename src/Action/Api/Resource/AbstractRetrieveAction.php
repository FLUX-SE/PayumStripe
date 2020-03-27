<?php

declare(strict_types=1);

namespace Prometee\PayumStripeCheckoutSession\Action\Api\Resource;

use Payum\Core\Exception\RequestNotSupportedException;
use Prometee\PayumStripeCheckoutSession\Action\Api\StripeApiAwareTrait;
use Prometee\PayumStripeCheckoutSession\Request\Api\Resource\RetrieveInterface;
use Stripe\ApiResource;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;

abstract class AbstractRetrieveAction implements RetrieveResourceActionInterface
{
    use StripeApiAwareTrait;

    /**
     * {@inheritDoc}
     *
     * @param RetrieveInterface $request
     *
     * @throws ApiErrorException
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $this->checkRequest($request);

        $id = (string) $request->getModel();

        Stripe::setApiKey($this->api->getSecretKey());
        $apiResource = $this->retrieveApiResource($id, $request->getOptions());
        $request->setApiResource($apiResource);
    }

    /**
     * {@inheritDoc}
     *
     * @throws ApiErrorException
     */
    public function retrieveApiResource(string $id, ?array $options = null): ApiResource
    {
        /** @var ApiResource $apiResource */
        $apiResource = $this->getApiResourceClass()::retrieve($id, $options);

        return $apiResource;
    }

    /**
     * {@inheritDoc}
     *
     * @param RetrieveInterface $request
     */
    public function supports($request)
    {
        return
            $request instanceof RetrieveInterface &&
            $this->supportAlso($request)
        ;
    }

    /**
     * @param RetrieveInterface $request
     */
    protected function checkRequest(RetrieveInterface $request)
    {
        // Silent is golden
    }
}
