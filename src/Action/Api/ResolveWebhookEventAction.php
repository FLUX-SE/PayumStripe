<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api;

use FluxSE\PayumStripe\Request\Api\ConstructEvent;
use FluxSE\PayumStripe\Request\Api\ResolveWebhookEvent;
use FluxSE\PayumStripe\Wrapper\EventWrapperInterface;
use LogicException;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\GetHttpRequest;
use Stripe\Exception\SignatureVerificationException;

class ResolveWebhookEventAction implements ActionInterface, GatewayAwareInterface, ApiAwareInterface
{
    use GatewayAwareTrait;
    use StripeApiAwareTrait;

    /** @var string[] */
    protected $signatureVerificationErrors = [];

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $httpRequest = new GetHttpRequest();
        $this->gateway->execute($httpRequest);

        $sigHeader = $this->retrieveStripeSignature($httpRequest);

        $payload = $httpRequest->content;

        $eventWrapper = $this->constructEvent($payload, $sigHeader);

        if (null === $eventWrapper) {
            /**
             * In case no event has been retrieve we stop here. This means no webhook secret
             * keys can be used to construct the event or something wrong append.
             *
             * Tip: This also allow other webhook consumers to process this $request if
             *      they are supporting the same type of `ResolveWebhookEvent` request
             */
            $signatureResults = implode(', ', $this->signatureVerificationErrors);
            $subException = RequestNotSupportedException::create($request);
            throw new RequestNotSupportedException(sprintf('Unable to resolve the webhook event payload with
                    one of your webhook secret keys ! Signature results : %s', $signatureResults), 0, $subException);
        }

        $request->setEventWrapper($eventWrapper);
    }

    protected function retrieveStripeSignature(GetHttpRequest $httpRequest): string
    {
        /*
         * 1. `GetHttpRequest` has been intercepted by the Symfony bridge action.
         * The `headers` property is normally not available into Payum core `GetHttpRequest`
         * object, but it's existing into the payum Symfony bridge one.
         *
         * @see \Payum\Core\Bridge\Symfony\Action\GetHttpRequestAction::updateRequest()
         */
        if (
            isset($httpRequest->headers)
            && count($httpRequest->headers) > 0
            && isset($httpRequest->headers['stripe-signature'])
        ) {
            return current($httpRequest->headers['stripe-signature']);
        }

        /**
         * 2. `GetHttpRequest` has been intercepted by the PlainPhp bridge action
         * and we can't get the header into $httpRequest so we try to found it
         * into $_SERVER. Useful when using plain PHP.
         *
         * @see \Payum\Core\Bridge\PlainPhp\Action\GetHttpRequestAction::execute()
         */
        if (!empty($_SERVER['HTTP_STRIPE_SIGNATURE'])) {
            return $_SERVER['HTTP_STRIPE_SIGNATURE'];
        }

        throw new LogicException('A Stripe header signature is required !');
    }

    protected function constructEvent(string $payload, string $sigHeader): ?EventWrapperInterface
    {
        foreach ($this->api->getWebhookSecretKeys() as $webhookSecretKey) {
            $eventRequest = new ConstructEvent($payload, $sigHeader, $webhookSecretKey);

            try {
                $this->gateway->execute($eventRequest);
            } catch (SignatureVerificationException $e) {
                $this->signatureVerificationErrors[] = sprintf(
                    '%s : %s',
                    $webhookSecretKey,
                    $e->getMessage()
                );
            }

            $eventWrapper = $eventRequest->getEventWrapper();

            if (null !== $eventWrapper) {
                return $eventWrapper;
            }
        }

        return null;
    }

    public function supports($request): bool
    {
        return $request instanceof ResolveWebhookEvent
            && EventWrapperInterface::class === $request->getTo()
            ;
    }
}
