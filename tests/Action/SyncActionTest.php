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
use Payum\Core\Request\Sync;
use PHPUnit\Framework\TestCase;
use Stripe\Checkout\Session;
use Stripe\PaymentIntent;
use Stripe\SetupIntent;
use Stripe\Subscription;

final class SyncActionTest extends TestCase
{
    use GatewayAwareTestTrait;

    public function testShouldImplements()
    {
        $action = new SyncAction();

        $this->assertInstanceOf(GatewayAwareInterface::class, $action);
        $this->assertInstanceOf(ActionInterface::class, $action);
        $this->assertNotInstanceOf(ApiAwareInterface::class, $action);
    }

    private function retrievePaymentIntentFromModel(array $model): void
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(RetrievePaymentIntent::class))
            ->will($this->returnCallback(function (RetrievePaymentIntent $request) {
                $this->assertIsString($request->getModel());
                $request->setApiResource(new PaymentIntent());
            }))
        ;

        $action = new SyncAction();
        $action->setGateway($gatewayMock);

        $request = new Sync($model);
        $action->execute($request);
    }

    private function retrieveSubscriptionFromModel(array $model): void
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(RetrieveSubscription::class))
            ->will($this->returnCallback(function (RetrieveSubscription $request) {
                $this->assertIsString($request->getModel());
                $request->setApiResource(new Subscription());
            }))
        ;

        $action = new SyncAction();
        $action->setGateway($gatewayMock);

        $request = new Sync($model);
        $action->execute($request);
    }

    private function retrieveSetupIntentFromModel(array $model): void
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(RetrieveSetupIntent::class))
            ->will($this->returnCallback(function (RetrieveSetupIntent $request) {
                $this->assertIsString($request->getModel());
                $request->setApiResource(new SetupIntent());
            }))
        ;

        $action = new SyncAction();
        $action->setGateway($gatewayMock);

        $request = new Sync($model);
        $action->execute($request);
    }

    public function testShouldRetrievePaymentIntentWhenSessionObjectContainsPaymentIntentId()
    {
        $model = [
            'object' => Session::OBJECT_NAME,
            'id' => 'sess_0001',
            PaymentIntent::OBJECT_NAME => 'pi_0001',
        ];

        $this->retrievePaymentIntentFromModel($model);
    }

    public function testShouldRetrieveAPaymentIntentFromAPaymentIntentObject()
    {
        $model = [
            'object' => PaymentIntent::OBJECT_NAME,
            'id' => 'pi_0001',
        ];

        $this->retrievePaymentIntentFromModel($model);
    }

    public function testShouldRetrieveSubscriptionWhenSessionObjectContainsSubscriptionId()
    {
        $model = [
            'object' => Session::OBJECT_NAME,
            'id' => 'sess_0001',
            Subscription::OBJECT_NAME => 'sub_0001',
        ];

        $this->retrieveSubscriptionFromModel($model);
    }

    public function testShouldRetrieveASubscriptionFromASubscriptionObject()
    {
        $model = [
            'object' => Subscription::OBJECT_NAME,
            'id' => 'sub_0001',
        ];

        $this->retrieveSubscriptionFromModel($model);
    }

    public function testShouldRetrieveSetupIntentWhenSessionObjectContainsSetupIntentId()
    {
        $model = [
            'object' => Session::OBJECT_NAME,
            'id' => 'sess_0001',
            SetupIntent::OBJECT_NAME => 'si_0001',
        ];

        $this->retrieveSetupIntentFromModel($model);
    }

    public function testShouldRetrieveASetupIntentFromASetupIntentObject()
    {
        $model = [
            'object' => SetupIntent::OBJECT_NAME,
            'id' => 'sub_0001',
        ];

        $this->retrieveSetupIntentFromModel($model);
    }

    public function testShouldThrowExceptionWhenObjectIsNotProvided()
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
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The synced object should have an "object" attribute !');
        $action->execute($request);
    }

    /**
     * @throws LogicException
     */
    public function testShouldThrowExceptionWhenIdIsNotProvided()
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
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The synced object should have a retrievable "id" attribute !');
        $action->execute($request);
    }

    public function testShouldRetrieveSessionWhenRetrievableSessionModeObjectIdIsNotProvided()
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
            ->will($this->returnCallback(function (RetrieveSession $request) use ($model) {
                $this->assertIsString($request->getModel());
                $request->setApiResource(new Session($model));
            }));

        $action = new SyncAction();
        $action->setGateway($gatewayMock);

        $request = new Sync($model);
        $action->execute($request);
    }
}
