<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Action\Api\StripeApiAwareTrait;
use FluxSE\PayumStripe\Request\Api\Resource\DeleteInterface;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Stripe\ApiResource;

abstract class AbstractDeleteAction implements DeleteResourceActionInterface
{
    use StripeApiAwareTrait;
    use ResourceAwareActionTrait;

    /**
     * @param DeleteInterface $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $apiResource = $this->deleteApiResource($request);

        $request->setApiResource($apiResource);
    }

    public function deleteApiResource(DeleteInterface $request): ApiResource
    {
        $service = $this->getService();
        if (false === method_exists($service, 'retrieve')) {
            throw new LogicException('This Stripe service does not have "retrieve" method !');
        }

        if (false === method_exists($service, 'delete')) {
            throw new LogicException('This Stripe service does not have "delete" method !');
        }

        $apiResource = $service->retrieve(
            $request->getId(),
            $request->getOptions()
        );

        return $apiResource->delete();
    }

    public function supports($request): bool
    {
        return
            $request instanceof DeleteInterface &&
            $this->supportAlso($request)
        ;
    }
}
