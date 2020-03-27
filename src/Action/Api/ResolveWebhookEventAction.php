<?php

declare(strict_types=1);

namespace Prometee\PayumStripeCheckoutSession\Action\Api;

use LogicException;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\GetHttpRequest;
use Prometee\PayumStripeCheckoutSession\Request\Api\ConstructEvent;
use Prometee\PayumStripeCheckoutSession\Request\Api\ResolveWebhookEvent;
use Prometee\PayumStripeCheckoutSession\Wrapper\EventWrapperInterface;
use Stripe\Event;
use Stripe\Stripe;

class ResolveWebhookEventAction implements ActionInterface, GatewayAwareInterface, ApiAwareInterface
{
    use GatewayAwareTrait,
        StripeApiAwareTrait;

    /**
     * {@inheritDoc}
     *
     * @param ResolveWebhookEvent $request
     *
     * @throws LogicException
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $httpRequest = new GetHttpRequest();
        $this->gateway->execute($httpRequest);

        $sigHeader = $this->retrieveStripeSignature($httpRequest);

        $payload = $httpRequest->content;

        Stripe::setApiKey($this->api->getSecretKey());

        $eventWrapper = $this->constructEvent($payload, $sigHeader);

        if (null === $eventWrapper) {
            /**
             * In case no $event has been retrieve we stop here. This means no webhook secret
             * keys can be used to construct the event or something wrong append.
             *
             * Tip: This also allow other webhook consumers to process this $request if
             *      they are supporting the same type of `ResolveWebhookEvent` request
             */
            RequestNotSupportedException::create($request);
        }

        $request->setEventWrapper($eventWrapper);
    }

    /**
     * @param GetHttpRequest $httpRequest
     *
     * @return string
     */
    protected function retrieveStripeSignature(GetHttpRequest $httpRequest): string
    {
        /**
         * 1. GetHttpRequest has been intercepted by the Symfony bridge action.
         * The `headers` property is normally not available into GetHttpRequest
         * object. But it's existing into the payum symfony bridge.
         *
         * @see \Payum\Core\Bridge\Symfony\Action\GetHttpRequestAction::updateRequest()
         */
        if (isset($httpRequest->headers) && count($httpRequest->headers) > 0) {
            return current($httpRequest->headers['stripe-signature']);
        }

        /**
         * 2. GetHttpRequest has been intercepted by the PlainPhp bridge action
         * and we can't get the header into $httpRequest so we try to found it
         * into $_SERVER. Useful when using plain PHP.
         *
         * @see \Payum\Core\Bridge\PlainPhp\Action\GetHttpRequestAction::execute()
         */
        if (!empty($_SERVER['HTTP_STRIPE_SIGNATURE'])) {
            return $_SERVER['HTTP_STRIPE_SIGNATURE'];
        }

        throw new LogicException('A Stripe signature is required !');
    }

    /**
     * @param string $payload
     * @param string $sigHeader
     *
     * @return EventWrapperInterface|null
     */
    protected function constructEvent(string $payload, string $sigHeader): ?EventWrapperInterface
    {
        foreach ($this->api->getWebhookSecretKeys() as $webhookSecretKey) {
            $eventRequest = new ConstructEvent($payload, $sigHeader, $webhookSecretKey);
            $this->gateway->execute($eventRequest);
            $eventWrapper = $eventRequest->getEventWrapper();
            if (null !== $eventWrapper) {
                return $eventWrapper;
            }
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof ResolveWebhookEvent
            && $request->getTo() === Event::class
            ;
    }
}
