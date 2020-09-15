<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Sync;
use FluxSE\PayumStripe\Request\Api\Resource\AbstractRetrieve;
use FluxSE\PayumStripe\Request\Api\Resource\RetrievePaymentIntent;
use FluxSE\PayumStripe\Request\Api\Resource\RetrieveSession;
use FluxSE\PayumStripe\Request\Api\Resource\RetrieveSetupIntent;
use FluxSE\PayumStripe\Request\Api\Resource\RetrieveSubscription;
use Stripe\Checkout\Session;
use Stripe\PaymentIntent;
use Stripe\SetupIntent;
use Stripe\Subscription;

class SyncAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * @var array<string, string>
     */
    protected $sessionModes = [
        PaymentIntent::OBJECT_NAME => RetrievePaymentIntent::class,
        Subscription::OBJECT_NAME => RetrieveSubscription::class,
        SetupIntent::OBJECT_NAME => RetrieveSetupIntent::class,
    ];

    /**
     * {@inheritDoc}
     *
     * @param Sync $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);
        $model = ArrayObject::ensureArrayObject($request->getModel());

        $objectName = (string) $model->offsetGet('object');
        if (empty($objectName)) {
            throw new LogicException(
                'The synced object should have an "object" attribute !'
            );
        }

        $id = (string) $model->offsetGet('id');
        if ($id === '') {
            throw new LogicException(
                'The synced object should have a retrievable "id" attribute !'
            );
        }

        $this->syncSession($model);

        $retrieveSessionModeObject = $this->findRetrievableSessionModeObject($model);

        if (null === $retrieveSessionModeObject) {
            // Case where Session mode is "subscription" and the customer
            // is canceling his payment
            return;
        }

        $this->gateway->execute($retrieveSessionModeObject);
        $sessionModeObject = $retrieveSessionModeObject->getApiResource();

        $model->exchangeArray($sessionModeObject->toArray());
    }

    /**
     * @param ArrayObject $model
     */
    private function syncSession(ArrayObject $model): void
    {
        $objectName = (string) $model->offsetGet('object');
        if ($objectName !== Session::OBJECT_NAME) {
            return;
        }

        // Needed only for subscription mode
        if (null !== $this->findSessionModeIdInSession($model)) {
            // so it's needed to skip this method when retrievable id is founded
            return;
        }
        // if not retrieve the newest session from it's id

        $sessionRequest = new RetrieveSession($model->offsetGet('id'));
        $this->gateway->execute($sessionRequest);
        $session = $sessionRequest->getApiResource();

        $model->exchangeArray($session->toArray());
    }

    /**
     * @param ArrayObject $model
     *
     * @return AbstractRetrieve|null
     */
    protected function findRetrievableSessionModeObject(ArrayObject $model): ?AbstractRetrieve
    {
        $objectName = (string) $model->offsetGet('object');
        if (Session::OBJECT_NAME === $objectName) {
            return $this->findSessionModeIdInSession($model);
        }

        return $this->findSessionModeIdInModeObject($model);
    }

    /**
     * @param ArrayObject $model
     *
     * @return AbstractRetrieve|null
     */
    private function findSessionModeIdInSession(ArrayObject $model): ?AbstractRetrieve
    {
        foreach ($this->sessionModes as $sessionObject => $retrieveRequest) {
            if (false === $model->offsetExists($sessionObject)) {
                continue;
            }

            $sessionModeId = $model->offsetGet($sessionObject);
            if ($sessionModeId === null || $sessionModeId === '') {
                continue;
            }

            return new $retrieveRequest((string) $sessionModeId);
        }

        return null;
    }

    /**
     * @param ArrayObject $model
     *
     * @return AbstractRetrieve|null
     */
    private function findSessionModeIdInModeObject(ArrayObject $model): ?AbstractRetrieve
    {
        $objectName = (string) $model->offsetGet('object');
        foreach ($this->sessionModes as $sessionModeObject => $retrieveRequest) {
            if ($sessionModeObject !== $objectName) {
                continue;
            }

            if (false === $model->offsetExists('id')) {
                return null;
            }

            $sessionModeId = $model->offsetGet('id');
            if ($sessionModeId === null || $sessionModeId === '') {
                return null;
            }

            return new $retrieveRequest((string) $sessionModeId);
        }

        return null;
    }

    /**
     * {@inheritDoc}
     *
     * @param Sync $request
     */
    public function supports($request): bool
    {
        return
            $request instanceof Sync &&
            $request->getModel() instanceof ArrayAccess
            ;
    }
}
