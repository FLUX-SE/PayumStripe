<?php

declare(strict_types=1);

namespace Prometee\PayumStripe\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Prometee\PayumStripe\Request\Api\ConstructEvent;
use Prometee\PayumStripe\Wrapper\EventWrapper;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class ConstructEventAction implements ActionInterface
{
    /**
     * {@inheritDoc}
     *
     * @param ConstructEvent $request
     *
     * @throws SignatureVerificationException
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $event = Webhook::constructEvent(
            $request->getPayload(),
            $request->getSigHeader(),
            $request->getWebhookSecretKey()
        );
        $eventWrapper = new EventWrapper(
            $request->getWebhookSecretKey(),
            $event
        );

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
