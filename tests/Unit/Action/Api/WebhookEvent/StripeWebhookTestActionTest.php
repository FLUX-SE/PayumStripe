<?php

declare(strict_types=1);

namespace Tests\FluxSE\PayumStripe\Unit\Action\Api\WebhookEvent;

use FluxSE\PayumStripe\Action\Api\WebhookEvent\AbstractWebhookEventAction;
use FluxSE\PayumStripe\Action\Api\WebhookEvent\StripeWebhookTestAction;
use FluxSE\PayumStripe\Request\Api\WebhookEvent\WebhookEvent;
use FluxSE\PayumStripe\Wrapper\EventWrapper;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Capture;
use PHPUnit\Framework\TestCase;
use Stripe\Event;
use Tests\FluxSE\PayumStripe\Unit\Action\GatewayAwareTestTrait;

final class StripeWebhookTestActionTest extends TestCase
{
    use GatewayAwareTestTrait;

    public function testShouldImplements(): void
    {
        $action = new StripeWebhookTestAction();

        $this->assertNotInstanceOf(ApiAwareInterface::class, $action);
        $this->assertInstanceOf(ActionInterface::class, $action);

        $this->assertInstanceOf(AbstractWebhookEventAction::class, $action);
    }

    public function testOnlyAcceptWebhookEventWithNotNullEventWrapper(): void
    {
        $action = new StripeWebhookTestAction();

        $supports = $action->supports(new Capture([]));
        $this->assertFalse($supports);

        $eventWrapper = new EventWrapper('', new Event());
        $webhookEvent = new WebhookEvent($eventWrapper);
        $webhookEvent->setModel([]);

        $supports = $action->supports($webhookEvent);
        $this->assertFalse($supports);
    }

    public function testShouldConsumeTheWebhookEvent(): void
    {
        $model = [
            'id' => 'evt_00000000000000',
            'type' => Event::SETUP_INTENT_CANCELED,
        ];

        $event = Event::constructFrom($model);

        $action = new StripeWebhookTestAction();
        $eventWrapper = new EventWrapper('', $event);
        $webhookEvent = new WebhookEvent($eventWrapper);

        $supports = $action->supports($webhookEvent);
        $this->assertTrue($supports);

        $this->expectException(HttpResponse::class);
        $action->execute($webhookEvent);
    }

    public function testShouldNotConsumeTheWebhookEvent(): void
    {
        $model = [
            'id' => 'evt_00000000000001',
            'type' => Event::SETUP_INTENT_CANCELED,
        ];

        $event = Event::constructFrom($model);

        $action = new StripeWebhookTestAction();
        $eventWrapper = new EventWrapper('', $event);
        $webhookEvent = new WebhookEvent($eventWrapper);

        $supports = $action->supports($webhookEvent);
        $this->assertFalse($supports);

        $this->expectException(RequestNotSupportedException::class);
        $action->execute($webhookEvent);
    }
}
