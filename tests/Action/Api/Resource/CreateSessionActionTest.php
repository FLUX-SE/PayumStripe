<?php

namespace Tests\FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Action\Api\Resource\CreateResourceActionInterface;
use FluxSE\PayumStripe\Action\Api\Resource\CreateSessionAction;
use FluxSE\PayumStripe\Api\KeysInterface;
use FluxSE\PayumStripe\Request\Api\Resource\CreateSession;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayInterface;
use PHPUnit\Framework\TestCase;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Tests\FluxSE\PayumStripe\Action\Api\ApiAwareActionTestTrait;

final class CreateSessionActionTest extends TestCase
{
    use ApiAwareActionTestTrait;

    /**
     * @test
     */
    public function shouldImplements()
    {
        $action = new CreateSessionAction();

        $this->assertInstanceOf(ApiAwareInterface::class, $action);
        $this->assertInstanceOf(ActionInterface::class, $action);
        $this->assertNotInstanceOf(GatewayInterface::class, $action);

        $this->assertInstanceOf(CreateResourceActionInterface::class, $action);
    }

    /**
     * @test
     */
    public function shouldCreateASession()
    {
        $model = [];

        $apiMock = $this->createApiMock();

        $action = new CreateSessionAction();
        $action->setApiClass(KeysInterface::class);
        $action->setApi($apiMock);

        $this->assertEquals(Session::class, $action->getApiResourceClass());

        $request = new CreateSession($model);

        $this->assertTrue($action->supportAlso($request));

        $this->expectException(ApiErrorException::class);

        $action->execute($request);
    }
}
