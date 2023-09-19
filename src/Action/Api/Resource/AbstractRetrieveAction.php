<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Action\Api\StripeApiAwareTrait;
use FluxSE\PayumStripe\Request\Api\Resource\RetrieveInterface;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Stripe\ApiResource;

abstract class AbstractRetrieveAction implements RetrieveResourceActionInterface
{
    use StripeApiAwareTrait;
    use ResourceAwareActionTrait;

    /**
     * @param RetrieveInterface $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $apiResource = $this->retrieveApiResource($request);

        $request->setApiResource($apiResource);
    }

    public function retrieveApiResource(RetrieveInterface $request): ApiResource
    {
        $service = $this->getService();
        if (false === method_exists($service, 'retrieve')) {
            throw new LogicException('This Stripe service does not have "retrieve" method !');
        }

        return $service->retrieve(
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
