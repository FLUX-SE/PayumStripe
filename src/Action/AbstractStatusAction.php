<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Request\Sync;

abstract class AbstractStatusAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * @param GetStatusInterface $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $syncRequest = new Sync($model);
        $this->gateway->execute($syncRequest);

        if (null !== $model->offsetGet('error')) {
            $request->markFailed();

            return;
        }

        if ($this->isMarkedStatus($request, $model)) {
            return;
        }

        $request->markUnknown();
    }

    abstract public function isMarkedStatus(GetStatusInterface $request, ArrayObject $model): bool;

    public function supports($request): bool
    {
        if (false === $request instanceof GetStatusInterface) {
            return false;
        }

        $model = $request->getModel();
        if (false === $model instanceof ArrayAccess) {
            return false;
        }

        return $this->getSupportedObjectName() === $model->offsetGet('object');
    }

    abstract public function getSupportedObjectName(): string;
}
