<?php

namespace Tests\FluxSE\PayumStripe\Unit\Action\StripeJs\Api;

use FluxSE\PayumStripe\Action\StripeJs\Api\RenderStripeJsAction;
use FluxSE\PayumStripe\Api\KeysAwareInterface;
use FluxSE\PayumStripe\Request\Api\ConstructEvent;
use FluxSE\PayumStripe\Request\StripeJs\Api\RenderStripeJs;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\RenderTemplate;
use PHPUnit\Framework\TestCase;
use Stripe\ApiResource;
use Stripe\Checkout\Session;
use Stripe\PaymentIntent;
use Stripe\SetupIntent;
use Tests\FluxSE\PayumStripe\Unit\Action\Api\ApiAwareActionTestTrait;
use Tests\FluxSE\PayumStripe\Unit\Action\GatewayAwareTestTrait;

final class RenderStripeJsActionTest extends TestCase
{
    use ApiAwareActionTestTrait;
    use GatewayAwareTestTrait;

    public function apiRessourcesList(): array
    {
        return [
            [PaymentIntent::class],
            [SetupIntent::class],
        ];
    }

    public function testShouldImplements(): void
    {
        $action = new RenderStripeJsAction('aTemplateName', ApiResource::class);

        $this->assertInstanceOf(ActionInterface::class, $action);
        $this->assertInstanceOf(ApiAwareInterface::class, $action);
        $this->assertInstanceOf(GatewayAwareInterface::class, $action);
    }

    /**
     * @dataProvider apiRessourcesList
     */
    public function testShouldSupportOnlyATypedRequestAndAnApiRessourceClass(string $apiResourceClass): void
    {
        $model = new $apiResourceClass();
        $action = new RenderStripeJsAction('aTemplateName', $apiResourceClass);

        $this->assertTrue($action->supports(new RenderStripeJs($model, '')));
        $this->assertFalse($action->supports(new RenderStripeJs(new Session(), '')));
        $this->assertFalse($action->supports(new ConstructEvent('', '', '')));
    }

    /**
     * @dataProvider apiRessourcesList
     */
    public function testShouldNotSupportObtainTokenRequestWithNotArrayAccessModel(string $apiResourceClass): void
    {
        $model = new $apiResourceClass();
        $actionUrl = '';
        $action = new RenderStripeJsAction('aTemplateName', $apiResourceClass);

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(RenderTemplate::class))
            ->willReturnCallback(function (RenderTemplate $request) use ($model, $actionUrl) {
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
            });

        $apiMock = $this->createApiMock(false);
        $apiMock
            ->expects($this->once())
            ->method('getPublishableKey')
            ->willReturn('')
        ;

        $action->setApiClass(KeysAwareInterface::class);
        $action->setGateway($gatewayMock);
        $action->setApi($apiMock);

        $request = new RenderStripeJs($model, $actionUrl);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $this->expectException(HttpResponse::class);
        $action->execute($request);
    }
}
