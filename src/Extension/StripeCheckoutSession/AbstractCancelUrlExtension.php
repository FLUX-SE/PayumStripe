<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Extension\StripeCheckoutSession;

use ArrayAccess;
use FluxSE\PayumStripe\Request\Api\Resource\AbstractCustomCall;
use Payum\Core\Extension\Context;
use Payum\Core\Extension\ExtensionInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Security\TokenAggregateInterface;
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

        /** @var mixed|GetStatusInterface|TokenAggregateInterface $request */
        $request = $context->getRequest();
        if (false === $request instanceof GetStatusInterface) {
            return;
        }

        if (false === $request->isNew()) {
            return;
        }

        $model = $request->getModel();
        if (false === $model instanceof ArrayAccess) {
            return;
        }

        if ($this->getSupportedObjectName() !== $model->offsetGet('object')) {
            return;
        }

        $gateway = $context->getGateway();

        // Avoid processing custom GetStatusInterface requests outside
        // a Payum controller consuming a token
        if (false === $this->isDuringCancelUrlCall($request, $gateway)) {
            return;
        }

        /** @var string $id */
        $id = $model->offsetGet('id') ?? '';
        $cancelRequest = $this->createNewRequest($id);
        try {
            $gateway->execute($cancelRequest);
        } catch (ApiErrorException $e) {
            // Failsafe
        }

        // Cancel the payment
        $request->markCanceled();
    }

    protected function isDuringCancelUrlCall(GetStatusInterface $request, GatewayInterface $gateway): bool
    {
        if (false === $request instanceof TokenAggregateInterface) {
            return false;
        }

        $token = $request->getToken();
        if (null === $token) {
            return false;
        }

        $targetUrl = $token->getTargetUrl() ?? '';
        if (empty($targetUrl)) {
            return false;
        }

        $getHttpRequest = new GetHttpRequest();
        $gateway->execute($getHttpRequest);

        return false !== strpos($targetUrl, $getHttpRequest->uri);
    }

    abstract public function getSupportedObjectName(): string;

    abstract public function createNewRequest(string $id): AbstractCustomCall;
}
