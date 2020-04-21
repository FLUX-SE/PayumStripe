<?php

declare(strict_types=1);

namespace Prometee\PayumStripeCheckoutSession\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Notify;
use Payum\Core\Request\Sync;
use Prometee\PayumStripeCheckoutSession\Request\Api\ResolveWebhookEvent;
use Prometee\PayumStripeCheckoutSession\Request\Api\WebhookEvent\WebhookEvent;

final class NotifyAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritDoc}
     *
     * @param Notify $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        if (null === $request->getToken()) {
            $this->executeWebhook();
        } else {
            $this->gateway->execute(new Sync($request->getModel()));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request): bool
    {
        return $request instanceof Notify;
    }

    /**
     * All webhooks will be handle by this method
     */
    private function executeWebhook(): void
    {
        $eventRequest = new ResolveWebhookEvent(null);
        $this->gateway->execute($eventRequest);

        $eventWrapper = $eventRequest->getEventWrapper();
        if (null === $eventWrapper) {
            throw new LogicException('The event wrapper should not be null !');
        }

        $this->gateway->execute(new WebhookEvent($eventWrapper));
    }
}
