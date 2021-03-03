<?php

declare(strict_types=1);

namespace Tests\FluxSE\PayumStripe\Action\Api\WebhookEvent;

use FluxSE\PayumStripe\Action\Api\WebhookEvent\AbstractPaymentAction;
use FluxSE\PayumStripe\Action\Api\WebhookEvent\CheckoutSessionCompletedAction;
use FluxSE\PayumStripe\Request\Api\WebhookEvent\WebhookEvent;
use FluxSE\PayumStripe\Wrapper\EventWrapper;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\Token;
use Payum\Core\Request\GetToken;
use Payum\Core\Request\Notify;
use PHPUnit\Framework\TestCase;
use Stripe\Event;
use Tests\FluxSE\PayumStripe\Action\GatewayAwareTestTrait;

final class AbstractPaymentActionTest extends TestCase
{
    use GatewayAwareTestTrait;

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
            'type' => '',
        ];

        $event = Event::constructFrom($model);
        $eventWrapper = new EventWrapper('', $event);
        $webhookEvent = new WebhookEvent($eventWrapper);
        $webhookEvent->setModel(null);

        $supports = $this->action->supports($webhookEvent);
        $this->assertFalse($supports);

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

        $supports = $this->action->supports($webhookEvent);
        $this->assertFalse($supports);

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

        $supports = $this->action->supports($webhookEvent);
        $this->assertFalse($supports);

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

        $supports = $this->action->supports($webhookEvent);
        $this->assertFalse($supports);

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

        $supports = $this->action->supports($webhookEvent);
        $this->assertFalse($supports);

        $this->expectException(RequestNotSupportedException::class);
        $this->action->execute($webhookEvent);
    }

    public function testShouldConsumeAWebhookEvent()
    {
        $model = [
            'id' => 'event_1',
            'data' => [
                'object' => [
                    'metadata' => [
                        'token_hash' => 'test_hash',
                    ],
                ],
            ],
            'type' => '',
        ];

        $event = Event::constructFrom($model);
        $token = new Token();

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->exactly(2))
            ->method('execute')
            ->withConsecutive(
                [$this->isInstanceOf(GetToken::class)],
                [$this->isInstanceOf(Notify::class)]
            )
            ->willReturnOnConsecutiveCalls(
                $this->returnCallback(function (GetToken $request) use ($token) {
                    $this->assertEquals('test_hash', $request->getHash());
                    $request->setToken($token);
                }),
                $this->returnCallback(function (Notify $request) use ($token) {
                    $this->assertEquals($token, $request->getToken());
                })
            );

        $this->action->setGateway($gatewayMock);
        $eventWrapper = new EventWrapper('', $event);
        $webhookEvent = new WebhookEvent($eventWrapper);

        $supports = $this->action->supports($webhookEvent);
        $this->assertTrue($supports);

        $this->action->execute($webhookEvent);
    }
}
