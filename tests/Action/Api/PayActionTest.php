<?php

namespace Tests\FluxSE\PayumStripe\Action\Api;

use FluxSE\PayumStripe\Action\Api\PayAction;
use FluxSE\PayumStripe\Api\KeysInterface;
use FluxSE\PayumStripe\Request\Api\Pay;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\RenderTemplate;
use PHPUnit\Framework\TestCase;
use Stripe\PaymentIntent;
use Tests\FluxSE\PayumStripe\Action\GatewayAwareTestTrait;

final class PayActionTest extends TestCase
{
    use ApiAwareActionTestTrait;
    use GatewayAwareTestTrait;

    public function testShouldImplements()
    {
        $action = new PayAction('aTemplateName');

        $this->assertInstanceOf(ActionInterface::class, $action);
        $this->assertInstanceOf(ApiAwareInterface::class, $action);
        $this->assertInstanceOf(GatewayAwareInterface::class, $action);
    }

    public function testShouldNotSupportObtainTokenRequestWithNotArrayAccessModel()
    {
        $model = new PaymentIntent();
        $actionUrl = '';
        $action = new PayAction('aTemplateName');

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(RenderTemplate::class))
            ->will($this->returnCallback(function (RenderTemplate $request) use ($model, $actionUrl) {
                $parameters = $request->getParameters();
                $this->assertEquals('aTemplateName', $request->getTemplateName());
                $this->assertIsArray($parameters);
                $this->assertArrayHasKey('model', $parameters);
                $this->assertArrayHasKey('publishable_key', $parameters);
                $this->assertArrayHasKey('action_url', $parameters);
                $this->assertEquals([
                    'model' => $model,
                    'publishable_key' => '',
                    'action_url' => $actionUrl,
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

        $request = new Pay($model, $actionUrl);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $this->expectException(HttpResponse::class);
        $action->execute($request);
    }
}
