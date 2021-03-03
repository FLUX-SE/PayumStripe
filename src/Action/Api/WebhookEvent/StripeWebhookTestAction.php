<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\WebhookEvent;

use FluxSE\PayumStripe\Request\Api\WebhookEvent\WebhookEvent;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Reply\HttpResponse;
use Stripe\Event;

/**
 * This class exists to avoid 500 error when testing the Stripe Webhook.
 */
final class StripeWebhookTestAction extends AbstractWebhookEventAction
{
    protected function getSupportedEventTypes(): array
    {
        return [
            Event::CHECKOUT_SESSION_COMPLETED,
            Event::PAYMENT_INTENT_CANCELED,
            Event::SETUP_INTENT_CANCELED,
        ];
    }

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        throw new HttpResponse('Webhook test succeeded !');
    }

    public function supports($request): bool
    {
        if (false === parent::supports($request)) {
            return false;
        }

        $id = $this->retrieveEventId($request);
        if ('evt_00000000000000' === $id) {
            return true;
        }

        return false;
    }

    private function retrieveEventId(WebhookEvent $request): ?string
    {
        $eventWrapper = $request->getEventWrapper();
        /*
         * This should never be true because the method
         * `$this->supportTypes()` already check this
         * @see AbstractWebhookEventAction::supportsTypes()
         */
        if (null === $eventWrapper) {
            return null;
        }

        return $eventWrapper->getEvent()->id;
    }
}
