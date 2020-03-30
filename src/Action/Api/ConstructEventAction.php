<?php

declare(strict_types=1);

namespace Prometee\PayumStripeCheckoutSession\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Prometee\PayumStripeCheckoutSession\Request\Api\ConstructEvent;
use Prometee\PayumStripeCheckoutSession\Wrapper\EventWrapper;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\Webhook;

class ConstructEventAction implements ActionInterface, ApiAwareInterface
{
    use StripeApiAwareTrait;

    /**
     * {@inheritDoc}
     *
     * @param ConstructEvent $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        Stripe::setApiKey($this->api->getSecretKey());

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
    public function supports($request)
    {
        return $request instanceof ConstructEvent
            && is_string($request->getModel())
            ;
    }
}
