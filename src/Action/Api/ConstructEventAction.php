<?php

declare(strict_types=1);

namespace Prometee\PayumStripeCheckoutSession\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Prometee\PayumStripeCheckoutSession\Request\Api\ConstructEvent;
use Prometee\PayumStripeCheckoutSession\Wrapper\EventWrapper;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class ConstructEventAction implements ActionInterface
{
    /**
     * {@inheritDoc}
     *
     * @param ConstructEvent $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        try {
            $event = Webhook::constructEvent(
                $request->getPayload(),
                $request->getSigHeader(),
                $request->getWebhookSecretKey()
            );
            $eventWrapper = new EventWrapper(
                $request->getWebhookSecretKey(),
                $event
            );
        } catch (SignatureVerificationException $e) {
            $eventWrapper = null;
        }

        $request->setEventWrapper($eventWrapper);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request): bool
    {
        return $request instanceof ConstructEvent
            && is_string($request->getModel())
            ;
    }
}
