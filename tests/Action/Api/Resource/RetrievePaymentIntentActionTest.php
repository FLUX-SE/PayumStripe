<?php

namespace Tests\Prometee\PayumStripeCheckoutSession\Action\Api\Resource;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayInterface;
use PHPUnit\Framework\TestCase;
use Prometee\PayumStripeCheckoutSession\Action\Api\Resource\RetrievePaymentIntentAction;
use Prometee\PayumStripeCheckoutSession\Action\Api\Resource\RetrieveResourceActionInterface;
use Prometee\PayumStripeCheckoutSession\Api\KeysInterface;
use Prometee\PayumStripeCheckoutSession\Request\Api\Resource\RetrievePaymentIntent;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Tests\Prometee\PayumStripeCheckoutSession\Action\Api\ApiAwareActionTestTrait;

class RetrievePaymentIntentActionTest extends TestCase
{
    use ApiAwareActionTestTrait;

    /**
     * @test
     */
    public function shouldImplements()
    {
        $action = new RetrievePaymentIntentAction();

        $this->assertInstanceOf(ApiAwareInterface::class, $action);
        $this->assertInstanceOf(ActionInterface::class, $action);
        $this->assertNotInstanceOf(GatewayInterface::class, $action);

        $this->assertInstanceOf(RetrieveResourceActionInterface::class, $action);
    }

    /**
     * @test
     */
    public function shouldRetrieveAPaymentIntent()
    {
        $model = 'pi_1';

        $apiMock = $this->createApiMock();

        $action = new RetrievePaymentIntentAction();
        $action->setApiClass(KeysInterface::class);
        $action->setApi($apiMock);

        $this->assertEquals(PaymentIntent::class, $action->getApiResourceClass());

        $request = new RetrievePaymentIntent($model);

        $this->assertTrue($action->supportAlso($request));

        $this->expectException(ApiErrorException::class);

        $action->execute($request);
    }
}
