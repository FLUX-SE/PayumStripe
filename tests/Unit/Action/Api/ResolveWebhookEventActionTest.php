<?php

namespace Tests\FluxSE\PayumStripe\Unit\Action\Api;

use FluxSE\PayumStripe\Action\Api\ResolveWebhookEventAction;
use FluxSE\PayumStripe\Api\KeysAwareInterface;
use FluxSE\PayumStripe\Request\Api\ConstructEvent;
use FluxSE\PayumStripe\Request\Api\ResolveWebhookEvent;
use FluxSE\PayumStripe\Wrapper\EventWrapper;
use FluxSE\PayumStripe\Wrapper\EventWrapperInterface;
use LogicException;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\Request\GetHttpRequest;
use PHPUnit\Framework\TestCase;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Tests\FluxSE\PayumStripe\Unit\Action\GatewayAwareTestTrait;

final class ResolveWebhookEventActionTest extends TestCase
{
    use ApiAwareActionTestTrait;
    use GatewayAwareTestTrait;

    public function testShouldImplements(): void
    {
        $action = new ResolveWebhookEventAction();

        $this->assertInstanceOf(ActionInterface::class, $action);
        $this->assertInstanceOf(ApiAwareInterface::class, $action);
        $this->assertInstanceOf(GatewayAwareInterface::class, $action);
    }

    public function testShouldThrowLogicExceptionWhenNoStripeSignatureIsFound(): void
    {
        $action = new ResolveWebhookEventAction();

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class));

        $apiMock = $this->createApiMock(false);

        $action->setApiClass(KeysAwareInterface::class);
        $action->setGateway($gatewayMock);
        $action->setApi($apiMock);

        $request = new ResolveWebhookEvent();

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('A Stripe header signature is required !');
        $action->execute($request);
    }

    public function testShouldThrowExceptionWhenSignatureFailed(): void
    {
        $action = new ResolveWebhookEventAction();

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->exactly(2))
            ->method('execute')
            ->withConsecutive(
                [$this->isInstanceOf(GetHttpRequest::class)],
                [$this->isInstanceOf(ConstructEvent::class)]
            )
            ->willReturnOnConsecutiveCalls(
                $this->returnCallback(function (GetHttpRequest $request) {
                    $request->headers = [
                        'stripe-signature' => ['stripeSignature'],
                    ];
                    $request->content = 'stripeContent';
                }),
                $this->throwException(SignatureVerificationException::factory(''))
            );

        $apiMock = $this->createApiMock(false);
        $apiMock
            ->expects($this->once())
            ->method('getWebhookSecretKeys')
            ->willReturn(['whsec_test'])
        ;

        $action->setApiClass(KeysAwareInterface::class);
        $action->setGateway($gatewayMock);
        $action->setApi($apiMock);

        $request = new ResolveWebhookEvent();

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $this->expectException(RequestNotSupportedException::class);
        $action->execute($request);
    }

    public function testShouldResolveWebhookEventWithSymfonyRequestBridge(): void
    {
        $action = new ResolveWebhookEventAction();

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->exactly(2))
            ->method('execute')
            ->withConsecutive(
                [$this->isInstanceOf(GetHttpRequest::class)],
                [$this->isInstanceOf(ConstructEvent::class)]
            )
            ->willReturnOnConsecutiveCalls(
                $this->returnCallback(function (GetHttpRequest $request) {
                    $request->headers = [
                        'stripe-signature' => ['stripeSignature'],
                    ];
                    $request->content = 'stripeContent';
                }),
                $this->returnCallback(function (ConstructEvent $request) {
                    $this->assertEquals('stripeContent', $request->getPayload());
                    $this->assertEquals('stripeSignature', $request->getSigHeader());
                    $this->assertEquals('whsec_test', $request->getWebhookSecretKey());
                    $request->setEventWrapper(new EventWrapper(
                        $request->getWebhookSecretKey(),
                        new Event()
                    ));
                })
            );

        $apiMock = $this->createApiMock(false);
        $apiMock
            ->expects($this->once())
            ->method('getWebhookSecretKeys')
            ->willReturn(['whsec_test'])
        ;

        $action->setApiClass(KeysAwareInterface::class);
        $action->setGateway($gatewayMock);
        $action->setApi($apiMock);

        $request = new ResolveWebhookEvent();

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $this->assertInstanceOf(EventWrapperInterface::class, $request->getEventWrapper());
        $this->assertInstanceOf(EventWrapperInterface::class, $request->getResult());
        $this->assertEquals($request->getEventWrapper(), $request->getResult());
    }

    public function testShouldResolveWebhookEventWithPlainPHP(): void
    {
        $action = new ResolveWebhookEventAction();

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->exactly(2))
            ->method('execute')
            ->withConsecutive(
                [$this->isInstanceOf(GetHttpRequest::class)],
                [$this->isInstanceOf(ConstructEvent::class)]
            )
            ->willReturnOnConsecutiveCalls(
                $this->returnCallback(function (GetHttpRequest $request) {
                    $_SERVER['HTTP_STRIPE_SIGNATURE'] = 'stripeSignature';
                    $request->content = 'stripeContent';
                }),
                $this->returnCallback(function (ConstructEvent $request) {
                    $this->assertEquals('stripeContent', $request->getPayload());
                    $this->assertEquals('stripeSignature', $request->getSigHeader());
                    $this->assertEquals('whsec_test', $request->getWebhookSecretKey());
                    $request->setEventWrapper(new EventWrapper(
                        $request->getWebhookSecretKey(),
                        new Event()
                    ));
                })
            );

        $apiMock = $this->createApiMock(false);
        $apiMock
            ->expects($this->once())
            ->method('getWebhookSecretKeys')
            ->willReturn(['whsec_test'])
        ;

        $action->setApiClass(KeysAwareInterface::class);
        $action->setGateway($gatewayMock);
        $action->setApi($apiMock);

        $request = new ResolveWebhookEvent();

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $this->assertInstanceOf(EventWrapperInterface::class, $request->getEventWrapper());
        $this->assertInstanceOf(EventWrapperInterface::class, $request->getResult());
        $this->assertEquals($request->getEventWrapper(), $request->getResult());
    }

    public function testShouldRequestNotSupportedExceptionWhenTheWebhookCanNotBeResolved(): void
    {
        $action = new ResolveWebhookEventAction();

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->exactly(2))
            ->method('execute')
            ->withConsecutive(
                [$this->isInstanceOf(GetHttpRequest::class)],
                [$this->isInstanceOf(ConstructEvent::class)]
            )
            ->willReturnOnConsecutiveCalls(
                $this->returnCallback(function (GetHttpRequest $request) {
                    $_SERVER['HTTP_STRIPE_SIGNATURE'] = 'stripeSignature';
                    $request->content = 'stripeContent';
                }),
                $this->returnCallback(function (ConstructEvent $request) {
                    $this->assertEquals('stripeContent', $request->getPayload());
                    $this->assertEquals('stripeSignature', $request->getSigHeader());
                    $this->assertEquals('whsec_test', $request->getWebhookSecretKey());
                    $request->setEventWrapper(null);
                })
            );

        $apiMock = $this->createApiMock(false);
        $apiMock
            ->expects($this->once())
            ->method('getWebhookSecretKeys')
            ->willReturn(['whsec_test'])
        ;

        $action->setApiClass(KeysAwareInterface::class);
        $action->setGateway($gatewayMock);
        $action->setApi($apiMock);

        $request = new ResolveWebhookEvent();

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $this->expectException(RequestNotSupportedException::class);
        $action->execute($request);
    }
}
