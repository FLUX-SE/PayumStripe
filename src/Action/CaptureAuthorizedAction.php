<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action;

use ArrayAccess;
use FluxSE\PayumStripe\Request\Api\Resource\CapturePaymentIntent;
use FluxSE\PayumStripe\Request\Api\Resource\UpdatePaymentIntent;
use FluxSE\PayumStripe\Request\CaptureAuthorized;
use LogicException;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Generic;
use Payum\Core\Request\Sync;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Payum\Core\Security\TokenInterface;
use Stripe\PaymentIntent;

final class CaptureAuthorizedAction implements ActionInterface, GatewayAwareInterface, GenericTokenFactoryAwareInterface
{
    use GatewayAwareTrait;
    use GenericTokenFactoryAwareTrait;

    /**
     * @param CaptureAuthorized $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = $request->getModel();

        $object = $model['object'] ?? null;
        if (PaymentIntent::OBJECT_NAME !== $object) {
            return;
        }

        $status = $model['status'] ?? null;
        if (PaymentIntent::STATUS_REQUIRES_CAPTURE !== $status) {
            return;
        }

        $id = $model['id'] ?? null;
        if (null === $id) {
            return;
        }

        $updatedModel = new ArrayObject();
        $token = $this->getRequestToken($request);
        $notifyToken = $this->tokenFactory->createNotifyToken(
            $token->getGatewayName(),
            $token->getDetails()
        );
        $this->embedNotifyTokenHash($updatedModel, $notifyToken);

        $updateRequest = new UpdatePaymentIntent($id, $updatedModel->toUnsafeArray());
        $this->gateway->execute($updateRequest);

        $captureRequest = new CapturePaymentIntent($id);
        $this->gateway->execute($captureRequest);

        $capturedModel = $captureRequest->getApiResource()->toArray();
        $model->exchangeArray($capturedModel);

        $requestSync = new Sync($capturedModel);
        $this->gateway->execute($requestSync);
    }

    public function embedNotifyTokenHash(ArrayObject $model, TokenInterface $token): void
    {
        $metadata = $model->offsetGet('metadata');
        if (null === $metadata) {
            $metadata = [];
        }

        $metadata['token_hash'] = $token->getHash();
        $model['metadata'] = $metadata;
    }

    protected function getRequestToken(Generic $request): TokenInterface
    {
        $token = $request->getToken();

        if (null === $token) {
            throw new LogicException('The request token should not be null !');
        }

        return $token;
    }

    public function supports($request): bool
    {
        if (false === $request instanceof CaptureAuthorized) {
            return false;
        }

        return $request->getModel() instanceof ArrayAccess;
    }
}
