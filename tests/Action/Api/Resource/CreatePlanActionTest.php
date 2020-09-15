<?php

namespace Tests\FluxSE\PayumStripe\Action\Api\Resource;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayInterface;
use PHPUnit\Framework\TestCase;
use FluxSE\PayumStripe\Action\Api\Resource\CreatePlanAction;
use FluxSE\PayumStripe\Action\Api\Resource\CreateResourceActionInterface;
use FluxSE\PayumStripe\Api\KeysInterface;
use FluxSE\PayumStripe\Request\Api\Resource\CreatePlan;
use Stripe\Exception\ApiErrorException;
use Stripe\Plan;
use Tests\FluxSE\PayumStripe\Action\Api\ApiAwareActionTestTrait;

final class CreatePlanActionTest extends TestCase
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
        $model = [];

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
