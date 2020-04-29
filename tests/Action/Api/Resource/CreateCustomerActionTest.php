<?php

namespace Tests\Prometee\PayumStripe\Action\Api\Resource;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayInterface;
use PHPUnit\Framework\TestCase;
use Prometee\PayumStripe\Action\Api\Resource\CreateCustomerAction;
use Prometee\PayumStripe\Action\Api\Resource\CreateResourceActionInterface;
use Prometee\PayumStripe\Api\KeysInterface;
use Prometee\PayumStripe\Request\Api\Resource\CreateCustomer;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;
use Tests\Prometee\PayumStripe\Action\Api\ApiAwareActionTestTrait;

final class CreateCustomerActionTest extends TestCase
{
    use ApiAwareActionTestTrait;

    /**
     * @test
     */
    public function shouldImplements()
    {
        $action = new CreateCustomerAction();

        $this->assertInstanceOf(ApiAwareInterface::class, $action);
        $this->assertInstanceOf(ActionInterface::class, $action);
        $this->assertNotInstanceOf(GatewayInterface::class, $action);

        $this->assertInstanceOf(CreateResourceActionInterface::class, $action);
    }

    /**
     * @test
     */
    public function shouldCreateACustomer()
    {
        $model = [];

        $apiMock = $this->createApiMock();

        $action = new CreateCustomerAction();
        $action->setApiClass(KeysInterface::class);
        $action->setApi($apiMock);

        $this->assertEquals(Customer::class, $action->getApiResourceClass());

        $request = new CreateCustomer($model);

        $this->assertTrue($action->supportAlso($request));

        $this->expectException(ApiErrorException::class);

        $action->execute($request);
    }
}
