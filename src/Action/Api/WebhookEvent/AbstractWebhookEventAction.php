<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\WebhookEvent;

use FluxSE\PayumStripe\Request\Api\WebhookEvent\WebhookEvent;
use FluxSE\PayumStripe\Wrapper\EventWrapperInterface;
use Payum\Core\Action\ActionInterface;
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
