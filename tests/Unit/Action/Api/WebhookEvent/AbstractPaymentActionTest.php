<?php

declare(strict_types=1);

namespace Tests\FluxSE\PayumStripe\Unit\Action\Api\WebhookEvent;

use FluxSE\PayumStripe\Action\Api\WebhookEvent\AbstractPaymentAction;
use FluxSE\PayumStripe\Request\Api\WebhookEvent\WebhookEvent;
use FluxSE\PayumStripe\Wrapper\EventWrapper;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\Token;
use Payum\Core\Request\GetToken;
use Payum\Core\Request\Notify;
use PHPUnit\Framework\TestCase;
use Stripe\Event;
use Tests\FluxSE\PayumStripe\Unit\Action\GatewayAwareTestTrait;

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

    public function testShouldThrowExceptionWhenNullEventWrapper(): void
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

    public function testShouldThrowExceptionWhenNullData(): void
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

    public function testShouldThrowExceptionWhenNullObject(): void
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

    public function testShouldThrowExceptionWhenNullMetadata(): void
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

    public function testShouldThrowExceptionWhenNullTokenHash(): void
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

    public function testShouldConsumeAWebhookEvent(): void
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
