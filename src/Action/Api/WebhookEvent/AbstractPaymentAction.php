<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\WebhookEvent;

use FluxSE\PayumStripe\Request\Api\WebhookEvent\WebhookEvent;
use FluxSE\PayumStripe\Token\TokenHashKeysInterface;
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
        /** @var StripeObject $sessionModeObject */
        $sessionModeObject = $this->retrieveSessionModeObject($request);

        // 1. Retrieve the token hash into the metadata
        /** @var string $tokenHash */
        $tokenHash = $this->retrieveTokenHash($sessionModeObject);

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

        if (null === $sessionModeObject) {
            return null;
        }

        if (false === $sessionModeObject instanceof StripeObject) {
            return null;
        }

        return $sessionModeObject;
    }

    private function retrieveTokenHash(StripeObject $sessionModeObject): ?string
    {
        $metadata = $sessionModeObject->offsetGet('metadata');
        if (null === $metadata) {
            return null;
        }

        $tokenHashMetadataKeyName = $this->getTokenHashMetadataKeyName();
        /** @var string|null $tokenHash */
        $tokenHash = $metadata->offsetGet($tokenHashMetadataKeyName);
        if (null === $tokenHash) {
            return null;
        }

        return $tokenHash;
    }

    public function getTokenHashMetadataKeyName(): string
    {
        return TokenHashKeysInterface::DEFAULT_TOKEN_HASH_KEY_NAME;
    }

    private function findTokenByHash(string $tokenHash): TokenInterface
    {
        $getTokenRequest = new GetToken($tokenHash);

        $this->gateway->execute($getTokenRequest);

        return $getTokenRequest->getToken();
    }

    public function supports($request): bool
    {
        if (false === parent::supports($request)) {
            return false;
        }

        $sessionModeObject = $this->retrieveSessionModeObject($request);
        if (null === $sessionModeObject) {
            return false;
        }

        $tokenHash = $this->retrieveTokenHash($sessionModeObject);
        if (null === $tokenHash) {
            return false;
        }

        return true;
    }
}
