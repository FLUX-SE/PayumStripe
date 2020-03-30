<?php

namespace Tests\Prometee\PayumStripeCheckoutSession\Action\Api\Resource;

use ArrayObject;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayInterface;
use PHPUnit\Framework\TestCase;
use Prometee\PayumStripeCheckoutSession\Action\Api\Resource\CreatePlanAction;
use Prometee\PayumStripeCheckoutSession\Action\Api\Resource\CreateResourceActionInterface;
use Prometee\PayumStripeCheckoutSession\Api\KeysInterface;
use Prometee\PayumStripeCheckoutSession\Request\Api\Resource\CreatePlan;
use Stripe\Exception\ApiErrorException;
use Stripe\Plan;
use Tests\Prometee\PayumStripeCheckoutSession\Action\Api\ApiAwareActionTestTrait;

class CreatePlanActionTest extends TestCase
{
    use ApiAwareActionTestTrait;

    /**
     * @test
     */
    public function shouldImplements()
    {
        $action = new CreatePlanAction();

        $this->assertInstanceOf(ApiAwareInterface::class, $action);
        $this->assertInstanceOf(ActionInterface::class, $action);
        $this->assertNotInstanceOf(GatewayInterface::class, $action);

        $this->assertInstanceOf(CreateResourceActionInterface::class, $action);
    }

    /**
     * @test
     */
    public function shouldCreateAPlan()
    {
        $model = new ArrayObject([]);

        $apiMock = $this->createApiMock();

        $action = new CreatePlanAction();
        $action->setApiClass(KeysInterface::class);
        $action->setApi($apiMock);

        $this->assertEquals(Plan::class, $action->getApiResourceClass());

        $request = new CreatePlan($model);

        $this->assertTrue($action->supportAlso($request));

        $this->expectException(ApiErrorException::class);

        $action->execute($request);
    }
}
