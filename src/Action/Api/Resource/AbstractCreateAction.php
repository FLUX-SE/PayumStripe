<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Action\Api\StripeApiAwareTrait;
use FluxSE\PayumStripe\Request\Api\Resource\CreateInterface;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Stripe\ApiResource;

abstract class AbstractCreateAction implements CreateResourceActionInterface
{
    use StripeApiAwareTrait;
    use ResourceAwareActionTrait;

    /**
     * @param CreateInterface $request
     */
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
        $service = $this->getService();
        if (false === method_exists($service, 'create')) {
            throw new LogicException('This Stripe service does not have "create" method !');
        }

        return $service->create(
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
