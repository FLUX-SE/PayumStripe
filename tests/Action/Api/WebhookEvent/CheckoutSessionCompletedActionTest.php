<?php

namespace Tests\Prometee\PayumStripeCheckoutSession\Action\Api\WebhookEvent;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\Model\Payment;
use Payum\Core\Model\Token;
use Payum\Core\Request\GetBinaryStatus;
use Payum\Core\Request\GetToken;
use Payum\Core\Request\Sync;
use PHPUnit\Framework\TestCase;
use Prometee\PayumStripeCheckoutSession\Action\Api\WebhookEvent\AbstractPaymentAction;
use Prometee\PayumStripeCheckoutSession\Action\Api\WebhookEvent\AbstractWebhookEventAction;
use Prometee\PayumStripeCheckoutSession\Action\Api\WebhookEvent\CheckoutSessionCompletedAction;
use Prometee\PayumStripeCheckoutSession\Request\Api\WebhookEvent\WebhookEvent;
use Prometee\PayumStripeCheckoutSession\Request\DeleteWebhookToken;
use Prometee\PayumStripeCheckoutSession\Wrapper\EventWrapper;
use Stripe\Event;
use Tests\Prometee\PayumStripeCheckoutSession\Action\GatewayAwareTestTrait;

class CheckoutSessionCompletedActionTest extends TestCase
{
    use GatewayAwareTestTrait;

    /**
     * @test
     */
    public function shouldImplements()
    {
        $action = new CheckoutSessionCompletedAction();

        $this->assertNotInstanceOf(ApiAwareInterface::class, $action);
        $this->assertInstanceOf(ActionInterface::class, $action);
        $this->assertInstanceOf(GatewayAwareInterface::class, $action);

        $this->assertInstanceOf(AbstractPaymentAction::class, $action);
        $this->assertInstanceOf(AbstractWebhookEventAction::class, $action);
    }

    /**
     * @test
     */
    public function shouldConsumeAWebhookEvent()
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
            'type' => Event::CHECKOUT_SESSION_COMPLETED,
        ];

        $event = Event::constructFrom($model);
        $token = new Token();
        $payment = new Payment();

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(GetToken::class))
            ->will($this->returnCallback(function (GetToken $request) use ($token) {
                $this->assertEquals('test_hash', $request->getHash());
                $request->setToken($token);
            }));
        $gatewayMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf(GetBinaryStatus::class))
            ->will($this->returnCallback(function (GetBinaryStatus $request) use ($token, $payment) {
                $this->assertEquals($token, $request->getToken());
                $request->setModel($payment);
            }));
        $gatewayMock
            ->expects($this->at(2))
            ->method('execute')
            ->with($this->isInstanceOf(Sync::class))
            ->will($this->returnCallback(function (Sync $request) use ($event) {
                $this->assertInstanceOf(ArrayObject::class, $request->getModel());
            }));
        $gatewayMock
            ->expects($this->at(3))
            ->method('execute')
            ->with($this->isInstanceOf(DeleteWebhookToken::class))
            ->will($this->returnCallback(function (DeleteWebhookToken $request) use ($token) {
                $this->assertEquals($token, $request->getToken());
            }));


        $action = new CheckoutSessionCompletedAction();
        $action->setGateway($gatewayMock);
        $eventWrapper = new EventWrapper('', $event);
        $webhookEvent = new WebhookEvent($eventWrapper);
        $action->execute($webhookEvent);
    }
}
