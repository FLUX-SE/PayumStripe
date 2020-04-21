<?php

namespace Tests\Prometee\PayumStripeCheckoutSession\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\Model\Token;
use Payum\Core\Request\Notify;
use Payum\Core\Request\Sync;
use PHPUnit\Framework\TestCase;
use Prometee\PayumStripeCheckoutSession\Action\NotifyAction;
use Prometee\PayumStripeCheckoutSession\Request\Api\ResolveWebhookEvent;
use Prometee\PayumStripeCheckoutSession\Request\Api\WebhookEvent\WebhookEvent;
use Prometee\PayumStripeCheckoutSession\Wrapper\EventWrapper;
use Stripe\Event;

class NotifyActionTest extends TestCase
{
    use GatewayAwareTestTrait;

    /**
     * @test
     */
    public function shouldImplements()
    {
        $action = new NotifyAction();

        $this->assertInstanceOf(GatewayAwareInterface::class, $action);
        $this->assertInstanceOf(ActionInterface::class, $action);
        $this->assertNotInstanceOf(ApiAwareInterface::class, $action);
    }

    /**
     * @test
     */
    public function shouldExecuteResolveWebhookEventWhenNotifyUnsafeIsCalled()
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
        $action = new NotifyAction();
        $action->setGateway($gatewayMock);

        $request = new Notify(null);
        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldExecuteSyncWhenNotifyIsCalled()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(Sync::class))
        ;

        $action = new NotifyAction();
        $action->setGateway($gatewayMock);

        $request = new Notify(new Token());
        $action->execute($request);
    }
}
