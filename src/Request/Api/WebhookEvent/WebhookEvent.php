<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Request\Api\WebhookEvent;

use FluxSE\PayumStripe\Wrapper\EventWrapperInterface;
use Payum\Core\Request\Generic;

final class WebhookEvent extends Generic
{
    public function __construct(EventWrapperInterface $model)
    {
        parent::__construct($model);
    }

    /**
     * @param EventWrapperInterface|null $eventWrapper
     */
    public function setEventWrapper(?EventWrapperInterface $eventWrapper): void
    {
        parent::setModel($eventWrapper);
    }

    /**
     * @return EventWrapperInterface|null
     */
    public function getEventWrapper(): ?EventWrapperInterface
    {
        if ($this->getModel() instanceof EventWrapperInterface) {
            return $this->getModel();
        }

        return null;
    }
}
