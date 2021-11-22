<?php

namespace Tests\FluxSE\PayumStripe\Action;

use FluxSE\PayumStripe\Action\NotifyAction;
use FluxSE\PayumStripe\Request\Api\ResolveWebhookEvent;
use FluxSE\PayumStripe\Request\Api\WebhookEvent\WebhookEvent;
use FluxSE\PayumStripe\Wrapper\EventWrapper;
use LogicException;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\Model\Token;
use Payum\Core\Request\Notify;
use Payum\Core\Request\Sync;
use PHPUnit\Framework\TestCase;
use Stripe\Event;

final class NotifyActionTest extends TestCase
{
    use GatewayAwareTestTrait;

    public function testShouldImplements(): void
    {
        $action = new NotifyAction();

        $this->assertInstanceOf(GatewayAwareInterface::class, $action);
        $this->assertInstanceOf(ActionInterface::class, $action);
        $this->assertNotInstanceOf(ApiAwareInterface::class, $action);
    }

    public function testShouldThrowExceptionWhenResolveWebhookEventReturnANullEventWrapper(): void
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->withConsecutive(
                [$this->isInstanceOf(ResolveWebhookEvent::class)]
            )
            ->willReturnOnConsecutiveCalls(
                $this->returnCallback(function (ResolveWebhookEvent $request) {
                    $request->setEventWrapper(null);
                })
            )
        ;

        $action = new NotifyAction();
        $action->setGateway($gatewayMock);

        $request = new Notify(null);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $this->expectException(LogicException::class);
        $action->execute($request);
    }

    public function testShouldExecuteResolveWebhookEventWhenNotifyUnsafeIsCalled(): void
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->exactly(2))
            ->method('execute')
            ->withConsecutive(
                [$this->isInstanceOf(ResolveWebhookEvent::class)],
                [$this->isInstanceOf(WebhookEvent::class)]
            )
            ->willReturnOnConsecutiveCalls(
                $this->returnCallback(function (ResolveWebhookEvent $request) {
                    $request->setEventWrapper(new EventWrapper('', new Event()));
                }),
                null
            )
        ;

        $action = new NotifyAction();
        $action->setGateway($gatewayMock);

        $request = new Notify(null);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);
    }

    public function testShouldExecuteSyncWhenNotifyIsCalled(): void
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(Sync::class))
        ;

        $action = new NotifyAction();
        $action->setGateway($gatewayMock);

        $request = new Notify(new Token());

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);
    }
}
