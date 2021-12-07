<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Extension\StripeCheckoutSession;

use ArrayAccess;
use FluxSE\PayumStripe\Request\Api\Resource\AbstractCustomCall;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Extension\Context;
use Payum\Core\Extension\ExtensionInterface;
use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Request\Sync;
use Stripe\Exception\ApiErrorException;

abstract class AbstractCancelUrlExtension implements ExtensionInterface
{
    public function onPreExecute(Context $context): void
    {
    }

    public function onExecute(Context $context): void
    {
    }

    public function onPostExecute(Context $context): void
    {
        if ([] !== $context->getPrevious()) {
            return;
        }

        if (null !== $context->getException()) {
            return;
        }

        /** @var mixed|GetStatusInterface $request */
        $request = $context->getRequest();
        if (false === $request instanceof GetStatusInterface) {
            return;
        }

        // Avoid processing custom GetStatusInterface requests
        // outside a Payum controller consuming a token
        if (null === $request->getToken()) {
            return;
        }

        if (false === $request->isNew()) {
            return;
        }

        if (false === $request->getModel() instanceof ArrayAccess) {
            return;
        }

        $model = ArrayObject::ensureArrayObject($request->getModel());
        if ($this->getSupportedObjectName() !== $model->offsetGet('object')) {
            return;
        }

        $id = $model->offsetGet('id');
        $gateway = $context->getGateway();
        $cancelRequest = $this->createNewRequest($id);
        try {
            $gateway->execute($cancelRequest);
        } catch (ApiErrorException $e) {
            // Failsafe
        }

        // Cancel the payment
        $request->markCanceled();
    }

    abstract public function getSupportedObjectName(): string;

    abstract public function createNewRequest(string $id): AbstractCustomCall;
}