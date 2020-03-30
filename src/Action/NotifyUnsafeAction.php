<?php

declare(strict_types=1);

namespace Prometee\PayumStripeCheckoutSession\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Notify;
use Prometee\PayumStripeCheckoutSession\Request\Api\ResolveWebhookEvent;
use Prometee\PayumStripeCheckoutSession\Request\Api\WebhookEvent\WebhookEvent;

final class NotifyUnsafeAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritDoc}
     *
     * @param Notify $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $eventRequest = new ResolveWebhookEvent($request->getToken());
        $this->gateway->execute($eventRequest);

        $this->gateway->execute(new WebhookEvent($eventRequest->getEventWrapper()));
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Notify &&
            $request->getModel() === null
            ;
    }
}
