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
use Stripe\StripeObject;

abstract class AbstractPaymentAction extends AbstractWebhookEventAction implements GatewayAwareInterface
{
    use GatewayAwareTrait;

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        // 0. Retrieve the Session|PaymentIntent|SetupIntent into the WebhookEvent
        $sessionModeObject = $this->retrieveSessionModeObject($request);
        if (null === $sessionModeObject) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        // 1. Retrieve the token hash into the metadata
        $tokenHash = $this->retrieveTokenHash($sessionModeObject);
        if (null === $tokenHash) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        // 2. Try to found the Token
        $token = $this->findTokenByHash($tokenHash);

        // 3. Redirect to the notify URL
        $this->gateway->execute(new Notify($token));
    }

    protected function retrieveSessionModeObject(WebhookEvent $request): ?StripeObject
    {
        $eventWrapper = $request->getEventWrapper();
        if (null === $eventWrapper) {
            return null;
        }

        /** @var StripeObject|null $stripeObject */
        $stripeObject = $eventWrapper->getEvent()->offsetGet('data');
        if (null === $stripeObject) {
            return null;
        }

        /** @var StripeObject|null $sessionModeObject */
        $sessionModeObject = $stripeObject->offsetGet('object');

        return $sessionModeObject;
    }

    private function retrieveTokenHash(StripeObject $sessionModeObject): ?string
    {
        $metadata = $sessionModeObject->offsetGet('metadata');
        if (null === $metadata) {
            return null;
        }

        /** @var string|null $tokenHash */
        $tokenHash = $metadata->offsetGet('token_hash');
        if (null === $tokenHash) {
            return null;
        }

        return $tokenHash;
    }

    private function findTokenByHash(string $tokenHash): TokenInterface
    {
        $getTokenRequest = new GetToken($tokenHash);

        $this->gateway->execute($getTokenRequest);

        return $getTokenRequest->getToken();
    }
}
