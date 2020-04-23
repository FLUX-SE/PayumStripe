<?php

declare(strict_types=1);

namespace Prometee\PayumStripe\Action\Api\WebhookEvent;

use Payum\Core\Action\ActionInterface;
use Prometee\PayumStripe\Request\Api\WebhookEvent\WebhookEvent;
use Prometee\PayumStripe\Wrapper\EventWrapperInterface;
use Stripe\Event;

abstract class AbstractWebhookEventAction implements ActionInterface
{
    /**
     * @return string[]
     */
    abstract protected function getSupportedEventTypes(): array;

    /**
     * {@inheritDoc}
     *
     * @param WebhookEvent $request
     */
    public function supports($request): bool
    {
        return $request instanceof WebhookEvent
            && $request->getEventWrapper() instanceof EventWrapperInterface
            && $request->getEventWrapper()->getEvent() instanceof Event
            && $this->supportsTypes($request)
        ;
    }

    /**
     * @param WebhookEvent $request
     *
     * @return bool
     */
    protected function supportsTypes(WebhookEvent $request): bool
    {
        $eventWrapper = $request->getEventWrapper();

        if (null === $eventWrapper) {
            return false;
        }

        return in_array($eventWrapper->getEvent()->type, $this->getSupportedEventTypes());
    }
}
