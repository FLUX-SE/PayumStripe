<?php

namespace Tests\FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Action\Api\Resource\RetrieveActionInterface;
use FluxSE\PayumStripe\Action\Api\Resource\RetrievePaymentIntentAction;
use FluxSE\PayumStripe\Api\KeysInterface;
use FluxSE\PayumStripe\Request\Api\Resource\RetrievePaymentIntent;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayInterface;
use PHPUnit\Framework\TestCase;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Tests\FluxSE\PayumStripe\Action\Api\ApiAwareActionTestTrait;

final class RetrievePaymentIntentActionTest extends TestCase
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

        $this->assertInstanceOf(RetrieveActionInterface::class, $action);
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
