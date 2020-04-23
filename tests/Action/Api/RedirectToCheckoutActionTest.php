<?php

namespace Tests\Prometee\PayumStripe\Action\Api;

use ArrayObject;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\RenderTemplate;
use PHPUnit\Framework\TestCase;
use Prometee\PayumStripe\Action\Api\RedirectToCheckoutAction;
use Prometee\PayumStripe\Api\KeysInterface;
use Prometee\PayumStripe\Request\Api\RedirectToCheckout;
use Tests\Prometee\PayumStripe\Action\GatewayAwareTestTrait;

class RedirectToCheckoutActionTest extends TestCase
{
    use ApiAwareActionTestTrait,
        GatewayAwareTestTrait;

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
}
