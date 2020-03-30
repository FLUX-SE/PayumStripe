<?php

namespace Tests\Prometee\PayumStripeCheckoutSession\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Storage\StorageInterface;
use PHPUnit\Framework\TestCase;
use Prometee\PayumStripeCheckoutSession\Action\DeleteWebhookTokenAction;
use Prometee\PayumStripeCheckoutSession\Request\DeleteWebhookToken;

class DeleteWebhookTokenActionTest extends TestCase
{
    /**
     * @test
     */
    public function shouldImplements()
    {
        $tokenStorage = $this->createMock(StorageInterface::class);
        $action = new DeleteWebhookTokenAction($tokenStorage);

        $this->assertInstanceOf(ActionInterface::class, $action);
        $this->assertNotInstanceOf(GatewayInterface::class, $action);
        $this->assertNotInstanceOf(ApiAwareInterface::class, $action);
    }

    /**
     * @test
     */
    public function shouldCallDeleteTokenOnStorageInterface()
    {
        $token = $this->createMock(TokenInterface::class);

        $tokenStorage = $this->createMock(StorageInterface::class);
        $tokenStorage
            ->expects($this->once())
            ->method('delete')
            ->with($token)
        ;

        $action = new DeleteWebhookTokenAction($tokenStorage);

        $request = new DeleteWebhookToken($token);
        $action->execute($request);

        $this->assertSame($token, $request->getToken());
    }
}
