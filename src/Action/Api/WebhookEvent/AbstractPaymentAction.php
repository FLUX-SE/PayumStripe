<?php

declare(strict_types=1);

namespace Prometee\PayumStripeCheckoutSession\Action\Api\WebhookEvent;

use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Request\GetToken;
use Payum\Core\Security\TokenInterface;
use Prometee\PayumStripeCheckoutSession\Request\Api\WebhookEvent\WebhookEvent;
use Stripe\Checkout\Session;
use Stripe\PaymentIntent;

abstract class AbstractPaymentAction extends AbstractWebhookEventAction implements GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritDoc}
     *
     * @param WebhookEvent $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $eventWrapper = $request->getEventWrapper();
        $event = $eventWrapper->getEvent();

        /** @var Session|PaymentIntent $sessionOrPaymentIntent */
        $sessionOrPaymentIntent = $event->data->offsetGet('object');

        // 1. Retrieve the token hash into the metadata
        $metadata = $sessionOrPaymentIntent->metadata;
        if (null === $metadata) {
            throw new LogicException(sprintf('Metadata on %s is required !', Session::class));
        }

        /** @var string|null $tokenHash */
        $tokenHash = $metadata->offsetGet('token_hash');
        if (null === $tokenHash) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        // 2. Try to found the Token
        $token = $this->findTokenByHash($tokenHash);

        // 3. Redirect to the notify URL
        throw new HttpRedirect($token->getTargetUrl());
    }

    /**
     * @param string $tokenHash
     *
     * @return TokenInterface
     */
    private function findTokenByHash(string $tokenHash): TokenInterface
    {
        $getTokenRequest = new GetToken($tokenHash);

        $this->gateway->execute($getTokenRequest);

        return $getTokenRequest->getToken();
    }
}
