<?php

namespace Tests\FluxSE\PayumStripe\Action\StripeCheckoutSession\Api;

use ArrayObject;
use FluxSE\PayumStripe\Action\StripeCheckoutSession\Api\RedirectToCheckoutAction;
use FluxSE\PayumStripe\Api\KeysInterface;
use FluxSE\PayumStripe\Request\StripeCheckoutSession\Api\RedirectToCheckout;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\RenderTemplate;
use PHPUnit\Framework\TestCase;
use Tests\FluxSE\PayumStripe\Action\Api\ApiAwareActionTestTrait;
use Tests\FluxSE\PayumStripe\Action\GatewayAwareTestTrait;

final class RedirectToCheckoutActionTest extends TestCase
{
    use ApiAwareActionTestTrait;
    use GatewayAwareTestTrait;

    public function testShouldImplements()
    {
        $action = new RedirectToCheckoutAction('aTemplateName');

        $this->assertInstanceOf(ActionInterface::class, $action);
        $this->assertInstanceOf(ApiAwareInterface::class, $action);
        $this->assertInstanceOf(GatewayAwareInterface::class, $action);
    }

    public function testShouldNotSupportObtainTokenRequestWithNotArrayAccessModel()
    {
        $model = [];
        $action = new RedirectToCheckoutAction('aTemplateName');

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
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

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $this->expectException(HttpResponse::class);
        $action->execute($request);
    }
}
