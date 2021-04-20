<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action;

use FluxSE\PayumStripe\Request\Api\ResolveWebhookEvent;
use FluxSE\PayumStripe\Request\Api\WebhookEvent\WebhookEvent;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Generic;
use Payum\Core\Request\Notify;
use Payum\Core\Request\Sync;

class NotifyAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        if (null === $request->getToken()) {
            $this->executeWebhook();
        } else {
            $sync = new Sync($request->getModel());
            $this->gateway->execute($sync);
            $request->setModel($sync->getModel());
        }
    }

    /**
     * All webhooks will be handle by this method.
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

    public function supports($request): bool
    {
        return $request instanceof Notify;
    }
}
