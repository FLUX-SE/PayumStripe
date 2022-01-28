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

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);
        $model = ArrayObject::ensureArrayObject($request->getModel());

        $objectName = (string) $model->offsetGet('object');
        if (empty($objectName)) {
            throw new LogicException('The synced object must have an "object" attribute !');
        }

        $id = (string) $model->offsetGet('id');
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
        $objectName = (string) $model->offsetGet('object');
        if (Session::OBJECT_NAME !== $objectName) {
            return;
        }

        $sessionRequest = new RetrieveSession((string) $model->offsetGet('id'));
        $this->gateway->execute($sessionRequest);
        $session = $sessionRequest->getApiResource();

        $model->exchangeArray($session->toArray());
    }

    protected function findRetrievableSessionModeObject(ArrayObject $model): ?RetrieveInterface
    {
        $objectName = (string) $model->offsetGet('object');
        if (Session::OBJECT_NAME === $objectName) {
            return $this->findSessionModeIdInSession($model);
        }

        return $this->findSessionModeIdInModeObject($model);
    }

    protected function findSessionModeIdInSession(ArrayObject $model): ?RetrieveInterface
    {
        foreach ($this->sessionModes as $sessionObject => $retrieveRequest) {
            $sessionModeId = (string) $model->offsetGet($sessionObject);
            if (empty($sessionModeId)) {
                continue;
            }

            return new $retrieveRequest($sessionModeId);
        }

        return null;
    }

    protected function findSessionModeIdInModeObject(ArrayObject $model): ?RetrieveInterface
    {
        $objectName = (string) $model->offsetGet('object');
        foreach ($this->sessionModes as $sessionModeObject => $retrieveRequest) {
            if ($sessionModeObject !== $objectName) {
                continue;
            }

            $sessionModeId = (string) $model->offsetGet('id');

            return new $retrieveRequest($sessionModeId);
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
