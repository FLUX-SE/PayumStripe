<?php

namespace Tests\Prometee\PayumStripe\Action\Api\WebhookEvent;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\Model\Token;
use Payum\Core\Request\GetToken;
use Payum\Core\Request\Notify;
use PHPUnit\Framework\TestCase;
use Prometee\PayumStripe\Action\Api\WebhookEvent\AbstractPaymentAction;
use Prometee\PayumStripe\Action\Api\WebhookEvent\AbstractWebhookEventAction;
use Prometee\PayumStripe\Action\Api\WebhookEvent\PaymentIntentCanceledAction;
use Prometee\PayumStripe\Request\Api\WebhookEvent\WebhookEvent;
use Prometee\PayumStripe\Wrapper\EventWrapper;
use Stripe\Event;
use Tests\Prometee\PayumStripe\Action\GatewayAwareTestTrait;

final class PaymentIntentCanceledActionTest extends TestCase
{
    use GatewayAwareTestTrait;

    /**
     * @test
     */
    public function shouldImplements()
    {
        $action = new PaymentIntentCanceledAction();

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
            'type' => Event::PAYMENT_INTENT_CANCELED,
        ];

        $event = Event::constructFrom($model);
        $token = new Token();

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
            ->with($this->isInstanceOf(Notify::class))
            ->will($this->returnCallback(function (Notify $request) use ($token) {
                $this->assertEquals($token, $request->getToken());
            }))
        ;

        $action = new PaymentIntentCanceledAction();
        $action->setGateway($gatewayMock);
        $eventWrapper = new EventWrapper('', $event);
        $webhookEvent = new WebhookEvent($eventWrapper);

        $action->execute($webhookEvent);
    }
}
