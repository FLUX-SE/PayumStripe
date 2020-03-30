<?php

namespace Tests\Prometee\PayumStripeCheckoutSession\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Notify;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Prometee\PayumStripeCheckoutSession\Action\NotifyUnsafeAction;
use Prometee\PayumStripeCheckoutSession\Request\Api\ResolveWebhookEvent;
use Prometee\PayumStripeCheckoutSession\Request\Api\WebhookEvent\WebhookEvent;
use Prometee\PayumStripeCheckoutSession\Wrapper\EventWrapper;
use Stripe\Event;

class NotifyUnsafeActionTest extends TestCase
{
    /**
     * @test
     */
    public function shouldImplements()
    {
        $action = new NotifyUnsafeAction();

        $this->assertInstanceOf(GatewayAwareInterface::class, $action);
        $this->assertInstanceOf(ActionInterface::class, $action);
        $this->assertNotInstanceOf(ApiAwareInterface::class, $action);
    }

    /**
     * @test
     */
    public function shouldExecuteResolveWebhookEventThenGiveItToConsume()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(ResolveWebhookEvent::class))
            ->will($this->returnCallback(function (ResolveWebhookEvent $request) {
                $request->setEventWrapper(new EventWrapper('', new Event()));
            }))
        ;
        $gatewayMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf(WebhookEvent::class))
        ;
        $action = new NotifyUnsafeAction();
        $action->setGateway($gatewayMock);

        $request = new Notify(null);
        $action->execute($request);
    }

    /**
     * @return MockObject&GatewayInterface
     */
    protected function createGatewayMock(): GatewayInterface
    {
        return $this->createMock(GatewayInterface::class);
    }
}
