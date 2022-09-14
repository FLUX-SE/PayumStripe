<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action;

use ArrayAccess;
use FluxSE\PayumStripe\Request\Api\Resource\RetrieveInterface;
use FluxSE\PayumStripe\Request\Api\Resource\RetrievePaymentIntent;
use FluxSE\PayumStripe\Request\Api\Resource\RetrieveSession;
use FluxSE\PayumStripe\Request\Api\Resource\RetrieveSetupIntent;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Sync;
use Stripe\Checkout\Session;
use Stripe\PaymentIntent;
use Stripe\SetupIntent;

class SyncAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * @var array<string, string>
     */
    protected $sessionModes = [
        PaymentIntent::OBJECT_NAME => RetrievePaymentIntent::class,
        SetupIntent::OBJECT_NAME => RetrieveSetupIntent::class,
    ];

    /**
     * @param Sync $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        /** @var string|null $objectName */
        $objectName = $model->offsetGet('object');
        if (empty($objectName)) {
            throw new LogicException('The synced object must have an "object" attribute !');
        }

        /** @var string|null $id */
        $id = $model->offsetGet('id');
        if (empty($id)) {
            throw new LogicException('The synced object must have a retrievable "id" attribute !');
        }

        $this->syncSession($model);

        $retrieveRequest = $this->findRetrievableSessionModeObject($model);

        if (null === $retrieveRequest) {
            // Case where Session mode is "subscription" and
            // the customer is canceling his payment
            return;
        }

        $this->gateway->execute($retrieveRequest);
        $sessionModeObject = $retrieveRequest->getApiResource();

        $model->exchangeArray($sessionModeObject->toArray());
    }

    protected function syncSession(ArrayObject $model): void
    {
        /** @var string|null $objectName */
        $objectName = $model->offsetGet('object');
        if (Session::OBJECT_NAME !== $objectName) {
            return;
        }

        /** @var string|null $id */
        $id = $model->offsetGet('id');
        $sessionRequest = new RetrieveSession((string) $id);
        $this->gateway->execute($sessionRequest);
        $session = $sessionRequest->getApiResource();

        $model->exchangeArray($session->toArray());
    }

    protected function findRetrievableSessionModeObject(ArrayObject $model): ?RetrieveInterface
    {
        /** @var string|null $objectName */
        $objectName = $model->offsetGet('object');
        if (Session::OBJECT_NAME !== $objectName) {
            return $this->findSessionModeIdInModeObject($model);
        }

        /** @var string|null $mode */
        $mode = $model->offsetGet('mode');
        if (Session::MODE_SUBSCRIPTION === $mode) {
            return null;
        }

        return $this->findSessionModeIdInSession($model);
    }

    protected function findSessionModeIdInSession(ArrayObject $model): ?RetrieveInterface
    {
        foreach ($this->sessionModes as $sessionObject => $retrieveRequestClass) {
            /** @var string|null $sessionModeId */
            $sessionModeId = $model->offsetGet($sessionObject);
            if (empty($sessionModeId)) {
                continue;
            }

            /** @var RetrieveInterface $retrieveRequest */
            $retrieveRequest = new $retrieveRequestClass($sessionModeId);
            return $retrieveRequest;
        }

        return null;
    }

    protected function findSessionModeIdInModeObject(ArrayObject $model): ?RetrieveInterface
    {
        /** @var string|null $objectName */
        $objectName = $model->offsetGet('object');
        foreach ($this->sessionModes as $sessionModeObject => $retrieveRequestClass) {
            if ($sessionModeObject !== $objectName) {
                continue;
            }

            /** @var string|null $sessionModeId */
            $sessionModeId = $model->offsetGet('id');

            /** @var RetrieveInterface $retrieveRequest */
            $retrieveRequest = new $retrieveRequestClass((string)$sessionModeId);
            return $retrieveRequest;
        }

        return null;
    }

    public function supports($request): bool
    {
        if (false === $request instanceof Sync) {
            return false;
        }

        return $request->getModel() instanceof ArrayAccess;
    }
}
