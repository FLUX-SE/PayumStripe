<?php

namespace Tests\FluxSE\PayumStripe\Action\Api\WebhookEvent;

use FluxSE\PayumStripe\Action\Api\WebhookEvent\AbstractWebhookEventAction;
use FluxSE\PayumStripe\Request\Api\WebhookEvent\WebhookEvent;
use FluxSE\PayumStripe\Wrapper\EventWrapper;
use PHPUnit\Framework\TestCase;
use Stripe\Event;

class AbstractWebhookEventActionTest extends TestCase
{
    /** @var AbstractWebhookEventAction */
    private $action;

    protected function setUp(): void
    {
        $this->action = new class() extends AbstractWebhookEventAction {
            protected function getSupportedEventTypes(): array
            {
                return [''];
            }

            public function execute($request)
            {
            }
        };
    }

    /**
     * @test
     */
    public function shouldThrowExceptionWhenNullMetadata()
    {
        $event = Event::constructFrom([]);
        $eventWrapper = new EventWrapper('', $event);
        $webhookEvent = new WebhookEvent($eventWrapper);
        $webhookEvent->setModel(null);

        $this->assertFalse($this->action->supports($webhookEvent));
    }
}
