<?php

namespace Tests\Prometee\PayumStripeCheckoutSession\Action\Api;

use ArrayObject;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\RenderTemplate;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Prometee\PayumStripeCheckoutSession\Action\Api\RedirectToCheckoutAction;
use Prometee\PayumStripeCheckoutSession\Api\KeysInterface;
use Prometee\PayumStripeCheckoutSession\Request\Api\RedirectToCheckout;

class RedirectToCheckoutActionTest extends TestCase
{
    use ApiAwareActionTrait;

    /**
     * @test
     */
    public function shouldImplements()
    {
        $action = new RedirectToCheckoutAction('aTemplateName');

        $this->assertInstanceOf(ActionInterface::class, $action);
        $this->assertInstanceOf(ApiAwareInterface::class, $action);
        $this->assertInstanceOf(GatewayAwareInterface::class, $action);
    }

    /**
     * @test
     */
    public function shouldNotSupportObtainTokenRequestWithNotArrayAccessModel()
    {
        $model = [];
        $action = new RedirectToCheckoutAction('aTemplateName');

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(RenderTemplate::class))
            ->will($this->returnCallback(function (RenderTemplate $request) use ($model) {
                $parameters = $request->getParameters();
                $this->assertEquals('aTemplateName', $request->getTemplateName());
                $this->assertIsArray($parameters);
                $this->assertArrayHasKey('model', $parameters);
                $this->assertArrayHasKey('publishable_key', $parameters);
                $this->assertEquals([
                    'model' => new ArrayObject($model),
                    'publishable_key' => '',
                ], $parameters);
                $request->setResult('');
            }));

        $apiMock = $this->createApiMock(false);
        $apiMock
            ->expects($this->once())
            ->method('getPublishableKey')
            ->willReturn('')
        ;

        $action->setApiClass(KeysInterface::class);
        $action->setGateway($gatewayMock);
        $action->setApi($apiMock);

        $request = new RedirectToCheckout($model);

        $this->expectException(HttpResponse::class);

        $action->execute($request);
    }

    /**
     * @return MockObject&GatewayInterface
     */
    protected function createGatewayMock(): GatewayInterface
    {
        return $this->createMock(GatewayInterface::class);
    }
}
