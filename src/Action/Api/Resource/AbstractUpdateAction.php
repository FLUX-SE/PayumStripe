<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Action\Api\StripeApiAwareTrait;
use FluxSE\PayumStripe\Request\Api\Resource\UpdateInterface;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Stripe\ApiResource;

abstract class AbstractUpdateAction implements UpdateResourceActionInterface
{
    use StripeApiAwareTrait;
    use ResourceAwareActionTrait;

    /**
     * @param UpdateInterface $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $apiResources = $this->updateApiResource($request);

        $request->setApiResource($apiResources);
    }

    public function updateApiResource(UpdateInterface $request): ApiResource
    {
        $service = $this->getService();

        if (false === method_exists($service, 'update')) {
            throw new LogicException('This Stripe service does not have "update" method !');
        }

        return $service->update(
            $request->getId(),
            $request->getParameters(),
            $request->getOptions()
        );
    }

    public function supports($request): bool
    {
        return
            $request instanceof UpdateInterface &&
            $this->supportAlso($request)
        ;
    }
}
