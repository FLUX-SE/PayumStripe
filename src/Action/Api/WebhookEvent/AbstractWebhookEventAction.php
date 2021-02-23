<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\WebhookEvent;

use FluxSE\PayumStripe\Request\Api\WebhookEvent\WebhookEvent;
use Payum\Core\Action\ActionInterface;

abstract class AbstractWebhookEventAction implements ActionInterface
{
    /**
     * @return string[]
     */
    abstract protected function getSupportedEventTypes(): array;

    public function supports($request): bool
    {
        return $request instanceof WebhookEvent
            && $this->supportsTypes($request)
        ;
    }

    protected function supportsTypes(WebhookEvent $request): bool
    {
        $eventWrapper = $request->getEventWrapper();

        if (null === $eventWrapper) {
            return false;
        }

        return in_array($eventWrapper->getEvent()->type, $this->getSupportedEventTypes());
    }
}
