<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\WebhookEvent;

use FluxSE\PayumStripe\Request\Api\WebhookEvent\WebhookEvent;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\GetToken;
use Payum\Core\Request\Notify;
use Payum\Core\Security\TokenInterface;
use Stripe\Checkout\Session;
use Stripe\PaymentIntent;
use Stripe\SetupIntent;

abstract class AbstractPaymentAction extends AbstractWebhookEventAction implements GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var WebhookEvent $request */
        $eventWrapper = $request->getEventWrapper();

        $event = $eventWrapper->getEvent();

        /** @var Session|PaymentIntent|SetupIntent $sessionModeObject */
        $sessionModeObject = $event->data->offsetGet('object');

        // 1. Retrieve the token hash into the metadata
        $metadata = $sessionModeObject->metadata;
        if (null === $metadata) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        /** @var string|null $tokenHash */
        $tokenHash = $metadata->offsetGet('token_hash');
        if (null === $tokenHash) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        // 2. Try to found the Token
        $token = $this->findTokenByHash($tokenHash);

        // 3. Redirect to the notify URL
        $this->gateway->execute(new Notify($token));
    }

    private function findTokenByHash(string $tokenHash): TokenInterface
    {
        $getTokenRequest = new GetToken($tokenHash);

        $this->gateway->execute($getTokenRequest);

        return $getTokenRequest->getToken();
    }
}
