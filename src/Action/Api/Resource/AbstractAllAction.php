<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Action\Api\StripeApiAwareTrait;
use FluxSE\PayumStripe\Request\Api\Resource\AllInterface;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Stripe\Collection;

abstract class AbstractAllAction implements AllResourceActionInterface
{
    use StripeApiAwareTrait;
    use ResourceAwareActionTrait;

    /**
     * @param AllInterface $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $apiResources = $this->allApiResource($request);

        $request->setApiResources($apiResources);
    }

    /**
     * @throws LogicException
     */
    public function allApiResource(AllInterface $request): Collection
    {
        $service = $this->getService();
        if (false === method_exists($service, 'all')) {
            throw new LogicException('This Stripe service does not have "all" method !');
        }

        return $service->all(
            $request->getParameters(),
            $request->getOptions()
        );
    }

    public function supports($request): bool
    {
        return
            $request instanceof AllInterface &&
            $this->supportAlso($request)
        ;
    }
}
