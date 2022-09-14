<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api;

use FluxSE\PayumStripe\Request\Api\ConstructEvent;
use FluxSE\PayumStripe\Wrapper\EventWrapper;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class ConstructEventAction implements ActionInterface
{
    /**
     * {@inheritdoc}
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

    public function supports($request): bool
    {
        return $request instanceof ConstructEvent
            && is_string($request->getModel())
        ;
    }
}
