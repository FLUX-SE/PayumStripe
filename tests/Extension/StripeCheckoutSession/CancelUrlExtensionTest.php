<?php

namespace Tests\FluxSE\PayumStripe\Extension\StripeCheckoutSession;

use Exception;
use FluxSE\PayumStripe\Extension\StripeCheckoutSession\AbstractCancelUrlExtension;
use FluxSE\PayumStripe\Extension\StripeCheckoutSession\CancelUrlCancelPaymentIntentExtension;
use FluxSE\PayumStripe\Extension\StripeCheckoutSession\CancelUrlCancelSetupIntentExtension;
use FluxSE\PayumStripe\Extension\StripeCheckoutSession\CancelUrlExpireSessionExtension;
use FluxSE\PayumStripe\Request\Api\Resource\CancelPaymentIntent;
use FluxSE\PayumStripe\Request\Api\Resource\CancelSetupIntent;
use FluxSE\PayumStripe\Request\Api\Resource\CustomCallInterface;
use FluxSE\PayumStripe\Request\Api\Resource\ExpireSession;
use Payum\Core\Extension\Context;
use Payum\Core\Model\Token;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Request\Sync;
use PHPUnit\Framework\TestCase;
use Stripe\ApiResource;
use Stripe\Checkout\Session;
use Stripe\Exception\UnknownApiErrorException;
use Stripe\PaymentIntent;
use Stripe\SetupIntent;
use Tests\FluxSE\PayumStripe\Action\GatewayAwareTestTrait;

final class CancelUrlExtensionTest extends TestCase
{
    use GatewayAwareTestTrait;

    /**
     * @dataProvider extensionList
     *
     * @param class-string|AbstractCancelUrlExtension $extensionClass
     * @param class-string|ApiResource $apiResourceClass
     * @param class-string|CustomCallInterface $requestClass
     */
    public function testOnExecute(
        string $extensionClass,
        string $apiResourceClass,
        string $requestClass
    ): void {
        $request =  new GetHumanStatus([]);

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock->expects($this->never())
            ->method('execute');

        $context = new Context($gatewayMock, $request, []);

        /** @var AbstractCancelUrlExtension $extension */
        $extension = new $extensionClass();
        $extension->onExecute($context);
    }

    /**
     * @dataProvider extensionList
     *
     * @param class-string|AbstractCancelUrlExtension $extensionClass
     * @param class-string|ApiResource $apiResourceClass
     * @param class-string|CustomCallInterface $requestClass
     */
    public function testOnPreExecute(
        string $extensionClass,
        string $apiResourceClass,
        string $requestClass
    ): void {
        $request =  new GetHumanStatus([]);

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock->expects($this->never())
            ->method('execute');

        $context = new Context($gatewayMock, $request, []);

        /** @var AbstractCancelUrlExtension $extension */
        $extension = new $extensionClass();
        $extension->onPreExecute($context);
    }

    /**
     * @dataProvider extensionList
     *
     * @param class-string|AbstractCancelUrlExtension $extensionClass
     * @param class-string|ApiResource $apiResourceClass
     * @param class-string|CustomCallInterface $requestClass
     */
    public function testOnPostExecute(
        string $extensionClass,
        string $apiResourceClass,
        string $requestClass
    ): void {

        $model = [
            'id' => 'pi_1',
            'object' => $apiResourceClass::OBJECT_NAME,
        ];
        $uri = '/done.php?payum_token=123_45678-90abcdefghijklmnopqrstuvwxyz-ABCD';
        $token = new Token();
        $token->setTargetUrl('https://localhost'.$uri);

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->exactly(2))
            ->method('execute')
            ->withConsecutive(
                [$this->isInstanceOf(GetHttpRequest::class)],
                [$this->isInstanceOf($requestClass)],
            )
            ->willReturnOnConsecutiveCalls(
                $this->returnCallback(function (GetHttpRequest $request) use ($uri) {
                    $request->uri = $uri;
                })
            )
        ;

        $request = new GetHumanStatus($token);
        $request->markNew();
        $request->setModel($model);

        $context = new Context($gatewayMock, $request, []);

        /** @var AbstractCancelUrlExtension $extension */
        $extension = new $extensionClass();
        $extension->onPostExecute($context);

        $this->assertTrue($request->isCanceled());
    }

    /**
     * @dataProvider extensionList
     *
     * @param class-string|AbstractCancelUrlExtension $extensionClass
     * @param class-string|ApiResource $apiResourceClass
     * @param class-string|CustomCallInterface $requestClass
     */
    public function testOnPostExecuteDoNothing(
        string $extensionClass,
        string $apiResourceClass,
        string $requestClass
    ): void {
        $token = new Token();
        $request = new GetHumanStatus($token);
        $request->setModel([]);

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock->expects($this->never())
            ->method('execute');

        $context = new Context($gatewayMock, $request, []);
        $otherContext = new Context($gatewayMock, $request, [$context]);

        /** @var AbstractCancelUrlExtension $extension */
        $extension = new $extensionClass();

        // With previous context
        $extension->onPostExecute($otherContext);

        // With Exception
        $context->setException(new Exception('An exception'));
        $extension->onPostExecute($context);
        $context->setException();

        // With a Generic request
        $syncRequest = new Sync([]);
        $syncContext = new Context($gatewayMock, $syncRequest, []);
        $extension->onPostExecute($syncContext);

        // Without new status
        $extension->onPostExecute($context);

        $request->markNew();

        // Without ArrayAccess model
        $requestWithTokenAsModel = new GetHumanStatus(new Token());
        $requestWithTokenAsModel->markNew();
        $tokenAsModelContext = new Context($gatewayMock, $requestWithTokenAsModel, []);
        $extension->onPostExecute($tokenAsModelContext);

        // With an unknown model object
        $request->setModel(['object' => 'something_else']);
        $extension->onPostExecute($context);

        $model = [
            'object' => $apiResourceClass::OBJECT_NAME
        ];
        $request->setModel($model);

        // Not detecting a cancel_url request
        // - not instanceof TokenAggregateInterface
        $mockRequest = $this->createMock(GetStatusInterface::class);
        $mockRequest
            ->expects($this->once())
            ->method('isNew')
            ->willReturn(true)
        ;
        $mockRequest
            ->expects($this->once())
            ->method('getModel')
            ->willReturn($request->getModel())
        ;
        $notTokenAggregateContext = new Context($gatewayMock, $mockRequest, []);
        $extension->onPostExecute($notTokenAggregateContext);
        // - Token is null
        $nullTokenRequest = new GetHumanStatus($model);
        $nullTokenRequest->markNew();
        $nullTokenContext = new Context($gatewayMock, $nullTokenRequest, []);
        $extension->onPostExecute($nullTokenContext);

        //- targetUrl is empty
        $extension->onPostExecute($context);
    }

    /**
     * @dataProvider extensionList
     *
     * @param class-string|AbstractCancelUrlExtension $extensionClass
     * @param class-string|ApiResource $apiResourceClass
     * @param class-string|CustomCallInterface $requestClass
     */
    public function testOnPostExecuteExecuteGetHttpRequestButNothingElse(
        string $extensionClass,
        string $apiResourceClass,
        string $requestClass
    ): void {
        $model = [
            'object' => $apiResourceClass::OBJECT_NAME
        ];

        $wrongUri = '/done.php?payum_token=321-cba';
        $uri = '/done.php?payum_token=123_ABC';
        $token = new Token();
        $token->setTargetUrl('https://localhost'.$wrongUri);

        $request = new GetHumanStatus($token);
        $request->markNew();
        $request->setModel($model);

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class))
            ->willReturnCallback(function (GetHttpRequest $request) use ($uri) {
                $request->uri = $uri;
            })
        ;

        $context = new Context($gatewayMock, $request, []);

        /** @var AbstractCancelUrlExtension $extension */
        $extension = new $extensionClass();

        // cancel_url is different from the targetUrl
        $extension->onPostExecute($context);
    }

    /**
     * @dataProvider extensionList
     *
     * @param class-string|AbstractCancelUrlExtension $extensionClass
     * @param class-string|ApiResource $apiResourceClass
     * @param class-string|CustomCallInterface $requestClass
     */
    public function testOnPostExecuteThrowApiErrorExceptionOnSecondExecute(
        string $extensionClass,
        string $apiResourceClass,
        string $requestClass
    ): void {
        $model = [
            'id' => 'pi_1',
            'object' => $apiResourceClass::OBJECT_NAME,
        ];

        $uri = '/done.php?payum_token=123_ABC';
        $token = new Token();
        $token->setTargetUrl('https://localhost'.$uri);

        $request = new GetHumanStatus($token);
        $request->markNew();
        $request->setModel($model);

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->exactly(2))
            ->method('execute')
            ->withConsecutive(
                [$this->isInstanceOf(GetHttpRequest::class)],
                [$this->isInstanceOf($requestClass)],
            )
            ->willReturnOnConsecutiveCalls(
                $this->returnCallback(function (GetHttpRequest $request) use ($uri) {
                    $request->uri = $uri;
                }),
                $this->throwException(new UnknownApiErrorException('An exception !'))
            )
        ;

        $context = new Context($gatewayMock, $request, []);

        /** @var AbstractCancelUrlExtension $extension */
        $extension = new $extensionClass();

        $extension->onPostExecute($context);
    }

    public function extensionList(): array
    {
        return [
            [CancelUrlCancelPaymentIntentExtension::class, PaymentIntent::class, CancelPaymentIntent::class],
            [CancelUrlCancelSetupIntentExtension::class, SetupIntent::class, CancelSetupIntent::class],
            [CancelUrlExpireSessionExtension::class, Session::class, ExpireSession::class],
        ];
    }
}
