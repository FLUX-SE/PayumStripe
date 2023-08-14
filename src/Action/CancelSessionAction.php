<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action;

use ArrayAccess;
use FluxSE\PayumStripe\Request\Api\Resource\ExpireSession;
use FluxSE\PayumStripe\Token\TokenHashKeysInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Cancel;
use Payum\Core\Request\Generic;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Stripe\Checkout\Session;

final class CancelSessionAction implements ActionInterface, GatewayAwareInterface, GenericTokenFactoryAwareInterface
{
    use GatewayAwareTrait;
    use EmbeddableTokenTrait;

    /** @var string[] */
    public const CANCELABLE_STATUS = [
        Session::STATUS_OPEN,
    ];

    /**
     * @param Cancel $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var ArrayObject $model */
        $model = $request->getModel();
        if (false === $this->isCancelable($model)) {
            return;
        }

        $session = $this->prepareSession($request);
        if (null === $session) {
            return;
        }

        $cancelRequest = new ExpireSession($session->id);
        $this->gateway->execute($cancelRequest);

        /** @var Session $session */
        $session = $cancelRequest->getApiResource();
        $model->exchangeArray($session->toArray());
    }

    private function prepareSession(Generic $request): ?Session
    {
        $model = ArrayObject::ensureArrayObject($request->getModel());
        /** @var string|null $object */
        $object = $model->offsetGet('object');
        if (Session::OBJECT_NAME !== $object) {
            return null;
        }

        /** @var string|null $id */
        $id = $model['id'] ?? null;
        if (empty($id)) {
            return null;
        }

        $updatedModel = new ArrayObject();
        $this->embedNotifyTokenHash($updatedModel, $request);

        $updateRequest = new UpdateSession($id, $updatedModel->getArrayCopy());
        $this->gateway->execute($updateRequest);

        /** @var Session $Session */
        $Session = $updateRequest->getApiResource();

        return $Session;
    }

    /**
     * The token hash will be stored to a different
     * metadata key to avoid consuming the default one.
     */
    public function getTokenHashMetadataKeyName(): string
    {
        return TokenHashKeysInterface::CANCEL_TOKEN_HASH_KEY_NAME;
    }

    public function supports($request): bool
    {
        if (false === $request instanceof Cancel) {
            return false;
        }

        $model = $request->getModel();
        if (!$model instanceof ArrayAccess) {
            return false;
        }

        return Session::OBJECT_NAME === $model->offsetGet('object');
    }

    private function isCancelable(ArrayObject $model): bool
    {
        return in_array($model->offsetGet('status'), $this::CANCELABLE_STATUS, true);
    }
}
