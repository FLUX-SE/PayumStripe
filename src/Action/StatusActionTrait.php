<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action;

use ArrayAccess;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;

trait StatusActionTrait
{
    /**
     * @param GetStatusInterface $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

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
