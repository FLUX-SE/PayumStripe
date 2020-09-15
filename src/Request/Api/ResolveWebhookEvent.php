<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Request\Api;

use FluxSE\PayumStripe\Wrapper\EventWrapperInterface;
use Payum\Core\Request\Convert;
use Payum\Core\Security\TokenInterface;

final class ResolveWebhookEvent extends Convert
{
    /**
     * @param TokenInterface|null $token
     */
    public function __construct(TokenInterface $token = null)
    {
        parent::__construct(null, EventWrapperInterface::class, $token);
    }

    /**
     * @return EventWrapperInterface|null
     */
    public function getEventWrapper(): ?EventWrapperInterface
    {
        if ($this->getResult() instanceof EventWrapperInterface) {
            return $this->getResult();
        }

        return null;
    }

    /**
     * @param EventWrapperInterface|null $eventWrapper
     */
    public function setEventWrapper(?EventWrapperInterface $eventWrapper): void
    {
        $this->setResult($eventWrapper);
    }
}
