<?php

namespace Tests\Prometee\PayumStripe\Action\Api\Resource;

use ArrayObject;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayInterface;
use PHPUnit\Framework\TestCase;
use Prometee\PayumStripe\Action\Api\Resource\CreateResourceActionInterface;
use Prometee\PayumStripe\Action\Api\Resource\CreateSubscriptionAction;
use Prometee\PayumStripe\Api\KeysInterface;
use Prometee\PayumStripe\Request\Api\Resource\CreateSubscription;
use Stripe\Exception\ApiErrorException;
use Stripe\Subscription;
use Tests\Prometee\PayumStripe\Action\Api\ApiAwareActionTestTrait;

class CreateSubscriptionActionTest extends TestCase
{
    use ApiAwareActionTestTrait;

    /**
     * @test
     */
    public function shouldImplements()
    {
        $action = new CreateSubscriptionAction();

        $this->assertInstanceOf(ApiAwareInterface::class, $action);
        $this->assertInstanceOf(ActionInterface::class, $action);
        $this->assertNotInstanceOf(GatewayInterface::class, $action);

        $this->assertInstanceOf(CreateResourceActionInterface::class, $action);
    }

    /**
     * @test
     */
    public function shouldCreateASubscription()
    {
        $model = new ArrayObject([]);

        $apiMock = $this->createApiMock();

        $action = new CreateSubscriptionAction();
        $action->setApiClass(KeysInterface::class);
        $action->setApi($apiMock);

        $this->assertEquals(Subscription::class, $action->getApiResourceClass());

        $request = new CreateSubscription($model);

        $this->assertTrue($action->supportAlso($request));

        $this->expectException(ApiErrorException::class);

        $action->execute($request);
    }
}
