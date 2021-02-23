<?php

declare(strict_types=1);

namespace Tests\FluxSE\PayumStripe\Action\Api\WebhookEvent;

use FluxSE\PayumStripe\Action\Api\WebhookEvent\AbstractPaymentAction;
use FluxSE\PayumStripe\Request\Api\WebhookEvent\WebhookEvent;
use FluxSE\PayumStripe\Wrapper\EventWrapper;
use Payum\Core\Exception\RequestNotSupportedException;
use PHPUnit\Framework\TestCase;
use Stripe\Event;

final class AbstractPaymentActionTest extends TestCase
{
    /** @var AbstractPaymentAction */
    private $action;

    protected function setUp(): void
    {
        $this->action = new class() extends AbstractPaymentAction {
            protected function getSupportedEventTypes(): array
            {
                return [''];
            }
        };
    }

    public function testShouldThrowExceptionWhenNullEventWrapper()
    {
        $model = [
            'id' => 'event_1',
        ];

        $event = Event::constructFrom($model);
        $eventWrapper = new EventWrapper('', $event);
        $webhookEvent = new WebhookEvent($eventWrapper);
        $webhookEvent->setModel(null);

        $this->expectException(RequestNotSupportedException::class);
        $this->action->execute($webhookEvent);
    }

    public function testShouldThrowExceptionWhenNullData()
    {
        $model = [
            'id' => 'event_1',
            'data' => null,
            'type' => '',
        ];

        $event = Event::constructFrom($model);
        $eventWrapper = new EventWrapper('', $event);
        $webhookEvent = new WebhookEvent($eventWrapper);

        $this->expectException(RequestNotSupportedException::class);
        $this->action->execute($webhookEvent);
    }

    public function testShouldThrowExceptionWhenNullObject()
    {
        $model = [
            'id' => 'event_1',
            'data' => [
                'object' => null,
            ],
            'type' => '',
        ];

        $event = Event::constructFrom($model);
        $eventWrapper = new EventWrapper('', $event);
        $webhookEvent = new WebhookEvent($eventWrapper);

        $this->expectException(RequestNotSupportedException::class);
        $this->action->execute($webhookEvent);
    }

    public function testShouldThrowExceptionWhenNullMetadata()
    {
        $model = [
            'id' => 'event_1',
            'data' => [
                'object' => [
                    'metadata' => null,
                ],
            ],
            'type' => '',
        ];

        $event = Event::constructFrom($model);
        $eventWrapper = new EventWrapper('', $event);
        $webhookEvent = new WebhookEvent($eventWrapper);

        $this->expectException(RequestNotSupportedException::class);
        $this->action->execute($webhookEvent);
    }

    public function testShouldThrowExceptionWhenNullTokenHash()
    {
        $model = [
            'id' => 'event_1',
            'data' => [
                'object' => [
                    'metadata' => [
                        'token_hash' => null,
                    ],
                ],
            ],
            'type' => '',
        ];

        $event = Event::constructFrom($model);
        $eventWrapper = new EventWrapper('', $event);
        $webhookEvent = new WebhookEvent($eventWrapper);

        $this->expectException(RequestNotSupportedException::class);
        $this->action->execute($webhookEvent);
    }
}
