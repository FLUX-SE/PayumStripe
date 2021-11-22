<?php

namespace Tests\FluxSE\PayumStripe\Action;

use FluxSE\PayumStripe\Action\SyncAction;
use FluxSE\PayumStripe\Request\Api\Resource\RetrievePaymentIntent;
use FluxSE\PayumStripe\Request\Api\Resource\RetrieveSession;
use FluxSE\PayumStripe\Request\Api\Resource\RetrieveSetupIntent;
use FluxSE\PayumStripe\Request\Api\Resource\RetrieveSubscription;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\LogicException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Sync;
use PHPUnit\Framework\TestCase;
use Stripe\Checkout\Session;
use Stripe\PaymentIntent;
use Stripe\SetupIntent;
use Stripe\Subscription;

final class SyncActionTest extends TestCase
{
    use GatewayAwareTestTrait;

    public function testShouldImplements(): void
    {
        $action = new SyncAction();

        $this->assertInstanceOf(GatewayAwareInterface::class, $action);
        $this->assertInstanceOf(ActionInterface::class, $action);
        $this->assertNotInstanceOf(ApiAwareInterface::class, $action);
    }

    public function testSupports(): void
    {
        $action = new SyncAction();

        $request = new Capture([]);
        $supports = $action->supports($request);
        $this->assertFalse($supports);

        $request = new Sync(null);
        $supports = $action->supports($request);
        $this->assertFalse($supports);

        $request = new Sync([]);
        $supports = $action->supports($request);
        $this->assertTrue($supports);
    }

    private function retrievePaymentIntentFromModel(array $model): void
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(RetrievePaymentIntent::class))
            ->willReturnCallback(function (RetrievePaymentIntent $request) {
                $this->assertIsString($request->getModel());
                $request->setApiResource(new PaymentIntent());
            })
        ;

        $action = new SyncAction();
        $action->setGateway($gatewayMock);

        $request = new Sync($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);
    }

    private function retrieveSubscriptionFromModel(array $model): void
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(RetrieveSubscription::class))
            ->willReturnCallback(function (RetrieveSubscription $request) {
                $this->assertIsString($request->getModel());
                $request->setApiResource(new Subscription());
            })
        ;

        $action = new SyncAction();
        $action->setGateway($gatewayMock);

        $request = new Sync($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);
    }

    private function retrieveSetupIntentFromModel(array $model): void
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(RetrieveSetupIntent::class))
            ->willReturnCallback(function (RetrieveSetupIntent $request) {
                $this->assertIsString($request->getModel());
                $request->setApiResource(new SetupIntent());
            })
        ;

        $action = new SyncAction();
        $action->setGateway($gatewayMock);

        $request = new Sync($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);
    }

    public function testShouldRetrievePaymentIntentWhenSessionObjectContainsPaymentIntentId(): void
    {
        $model = [
            'object' => Session::OBJECT_NAME,
            'id' => 'sess_0001',
            PaymentIntent::OBJECT_NAME => 'pi_0001',
        ];

        $this->retrievePaymentIntentFromModel($model);
    }

    public function testShouldRetrieveAPaymentIntentFromAPaymentIntentObject(): void
    {
        $model = [
            'object' => PaymentIntent::OBJECT_NAME,
            'id' => 'pi_0001',
        ];

        $this->retrievePaymentIntentFromModel($model);
    }

    public function testShouldRetrieveSubscriptionWhenSessionObjectContainsSubscriptionId(): void
    {
        $model = [
            'object' => Session::OBJECT_NAME,
            'id' => 'sess_0001',
            Subscription::OBJECT_NAME => 'sub_0001',
        ];

        $this->retrieveSubscriptionFromModel($model);
    }

    public function testShouldRetrieveASubscriptionFromASubscriptionObject(): void
    {
        $model = [
            'object' => Subscription::OBJECT_NAME,
            'id' => 'sub_0001',
        ];

        $this->retrieveSubscriptionFromModel($model);
    }

    public function testShouldRetrieveSetupIntentWhenSessionObjectContainsSetupIntentId(): void
    {
        $model = [
            'object' => Session::OBJECT_NAME,
            'id' => 'sess_0001',
            SetupIntent::OBJECT_NAME => 'si_0001',
        ];

        $this->retrieveSetupIntentFromModel($model);
    }

    public function testShouldRetrieveASetupIntentFromASetupIntentObject(): void
    {
        $model = [
            'object' => SetupIntent::OBJECT_NAME,
            'id' => 'sub_0001',
        ];

        $this->retrieveSetupIntentFromModel($model);
    }

    public function testShouldOnlyTryToRetrieveASessionWhenTheIdIsNullOrEmpty(): void
    {
        $model = [
            'object' => Session::OBJECT_NAME,
            'id' => 'sess_0001',
            Subscription::OBJECT_NAME => null,
        ];

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(RetrieveSession::class))
            ->willReturnCallback(function (RetrieveSession $request) {
                $this->assertIsString($request->getModel());
                $request->setApiResource(new Session());
            })
        ;

        $action = new SyncAction();
        $action->setGateway($gatewayMock);

        $request = new Sync($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);
    }

    public function testShouldThrowExceptionWhenObjectIsNotProvided(): void
    {
        $model = [
            'id' => 'test_1',
        ];

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->never())
            ->method('execute')
        ;

        $action = new SyncAction();
        $action->setGateway($gatewayMock);

        $request = new Sync($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The synced object must have an "object" attribute !');
        $action->execute($request);
    }

    /**
     * @throws LogicException
     */
    public function testShouldThrowExceptionWhenIdIsNotProvided(): void
    {
        $model = [
            'object' => PaymentIntent::OBJECT_NAME,
        ];

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->never())
            ->method('execute')
        ;

        $action = new SyncAction();
        $action->setGateway($gatewayMock);

        $request = new Sync($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The synced object must have a retrievable "id" attribute !');
        $action->execute($request);
    }

    public function testShouldRetrieveSessionWhenRetrievableSessionModeObjectIdIsNotProvided(): void
    {
        $model = [
            'object' => Session::OBJECT_NAME,
            'id' => 'sess_0001',
        ];

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(RetrieveSession::class))
            ->willReturnCallback(function (RetrieveSession $request) use ($model) {
                $this->assertIsString($request->getModel());
                $request->setApiResource(new Session($model));
            });

        $action = new SyncAction();
        $action->setGateway($gatewayMock);

        $request = new Sync($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);
    }
}
