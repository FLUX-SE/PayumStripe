<?php

namespace Tests\FluxSE\PayumStripe\Action;

use FluxSE\PayumStripe\Action\StatusSessionAction;
use FluxSE\PayumStripe\Request\Api\Resource\AllInvoice;
use FluxSE\PayumStripe\Request\Api\Resource\RetrievePaymentIntent;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetBinaryStatus;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Request\Sync;
use PHPUnit\Framework\TestCase;
use Stripe\Checkout\Session;
use Stripe\Collection;
use Stripe\Invoice;
use Stripe\PaymentIntent;

final class StatusSessionActionTest extends TestCase
{
    use GatewayAwareTestTrait;

    public function testShouldImplements(): void
    {
        $action = new StatusSessionAction();

        $this->assertInstanceOf(ActionInterface::class, $action);
        $this->assertNotInstanceOf(GatewayInterface::class, $action);
        $this->assertNotInstanceOf(ApiAwareInterface::class, $action);
    }

    public function testSupportOnlyGetStatusInterfaceAndArrayAccessObject(): void
    {
        $action = new StatusSessionAction();

        $model = [
            'object' => Session::OBJECT_NAME,
        ];

        $support = $action->supports(new GetHumanStatus($model));
        $this->assertTrue($support);

        $support = $action->supports(new GetBinaryStatus($model));
        $this->assertTrue($support);

        $support = $action->supports(new GetHumanStatus(''));
        $this->assertFalse($support);

        $support = $action->supports(new Capture($model));
        $this->assertFalse($support);
    }

    public function testMarkNewIdStatusDifferentFromOpen(): void
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(Sync::class))
        ;

        $action = new StatusSessionAction();
        $action->setGateway($gatewayMock);

        $action = new StatusSessionAction();
        $action->setGateway($gatewayMock);

        $model = [
            'object' => Session::OBJECT_NAME,
            'status' => 'test',
            'payment_status' => '',
        ];

        $request = new GetHumanStatus($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $this->assertTrue($request->isUnknown());
    }

    public function testShouldMarkUnknownIfNoStatusIsFound(): void
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(Sync::class))
        ;

        $action = new StatusSessionAction();
        $action->setGateway($gatewayMock);

        $model = [
            'object' => Session::OBJECT_NAME,
        ];

        $request = new GetHumanStatus($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $this->assertTrue($request->isUnknown());
    }

    public function testShouldMarkFailedIfErrorIsFound(): void
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(Sync::class))
        ;

        $action = new StatusSessionAction();
        $action->setGateway($gatewayMock);

        $model = [
            'object' => Session::OBJECT_NAME,
            'error' => 'an error',
        ];

        $request = new GetHumanStatus($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $this->assertTrue($request->isFailed());
    }

    protected function createStatusWithGateway(): StatusSessionAction
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(Sync::class))
        ;

        $action = new StatusSessionAction();
        $action->setGateway($gatewayMock);

        return $action;
    }

    public function testShouldMarkAsNewIfIsASessionObjectIsOpenAndPaymentStatusIsUnpaid(): void
    {
        $action = $this->createStatusWithGateway();

        $model = [
            'object' => Session::OBJECT_NAME,
            'status' => Session::STATUS_OPEN,
            'payment_status' => Session::PAYMENT_STATUS_UNPAID,
        ];

        $request = new GetHumanStatus($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $this->assertTrue($request->isNew());
    }

    public function testShouldMarkAsNewIfIsASessionObjectIsOpenAndPaymentStatusIsNoPaymentRequired(): void
    {
        $action = $this->createStatusWithGateway();

        $model = [
            'object' => Session::OBJECT_NAME,
            'status' => Session::STATUS_OPEN,
            'payment_status' => Session::PAYMENT_STATUS_NO_PAYMENT_REQUIRED,
        ];

        $request = new GetHumanStatus($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $this->assertTrue($request->isNew());
    }

    public function testShouldMarkAsCapturedIfIsASessionObjectIsCompletedAndPaymentStatusIsNoPaymentRequired(): void
    {
        $action = $this->createStatusWithGateway();

        $model = [
            'object' => Session::OBJECT_NAME,
            'status' => Session::STATUS_COMPLETE,
            'payment_status' => Session::PAYMENT_STATUS_NO_PAYMENT_REQUIRED,
        ];

        $request = new GetHumanStatus($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $this->assertTrue($request->isCaptured());
    }

    public function testShouldMarkAsCapturedIfIsASessionObjectIsCompletedAndPaymentStatusIsPayed(): void
    {
        $action = $this->createStatusWithGateway();

        $model = [
            'object' => Session::OBJECT_NAME,
            'status' => Session::STATUS_COMPLETE,
            'payment_status' => Session::PAYMENT_STATUS_PAID,
        ];

        $request = new GetHumanStatus($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $this->assertTrue($request->isCaptured());
    }

    public function testShouldMarkAsExpiredIfIsASessionObjectIsExpired(): void
    {
        $action = $this->createStatusWithGateway();

        $model = [
            'object' => Session::OBJECT_NAME,
            'status' => Session::STATUS_EXPIRED,
            'payment_status' => Session::PAYMENT_STATUS_UNPAID,
        ];

        $request = new GetHumanStatus($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $this->assertTrue($request->isExpired());
    }

    public function testShouldMarkAsPendingIfIsASessionObjectIsCompletedAndPaymentStatusIsUnpaid(): void
    {
        $action = $this->createStatusWithGateway();

        $model = [
            'object' => Session::OBJECT_NAME,
            'status' => Session::STATUS_COMPLETE,
            'payment_status' => Session::PAYMENT_STATUS_UNPAID,
        ];

        $request = new GetHumanStatus($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $this->assertTrue($request->isPending());
    }

    public function testShouldMarkAsPendingIfIsASessionObjectIsCompletedAndPaymentStatusIsUnpaidInCaseOfASubscriptionWithoutInvoices(): void
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->exactly(2))
            ->method('execute')
            ->withConsecutive(
                [$this->isInstanceOf(Sync::class)],
                [$this->isInstanceOf(AllInvoice::class)]
            )
            ->willReturnOnConsecutiveCalls(
                null,
                $this->returnCallback(function (AllInvoice $request) {
                    $request->setApiResources(Collection::emptyCollection());
                })
            )
        ;

        $action = new StatusSessionAction();
        $action->setGateway($gatewayMock);

        $model = [
            'object' => Session::OBJECT_NAME,
            'status' => Session::STATUS_COMPLETE,
            'payment_status' => Session::PAYMENT_STATUS_UNPAID,
            'subscription' => 'sub_0001',
        ];

        $request = new GetHumanStatus($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $this->assertTrue($request->isPending());
    }

    public function testShouldMarkAsPendingIfIsASessionObjectIsCompletedAndPaymentStatusIsUnpaidInCaseOfASubscriptionWitInvoiceAndWithoutPaymentIntent(): void
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->exactly(2))
            ->method('execute')
            ->withConsecutive(
                [$this->isInstanceOf(Sync::class)],
                [$this->isInstanceOf(AllInvoice::class)]
            )
            ->willReturnOnConsecutiveCalls(
                null,
                $this->returnCallback(function (AllInvoice $request) {
                    $request->setApiResources(Collection::constructFrom([
                        'data' => [
                            [
                                'model' => Invoice::OBJECT_NAME,
                                'id' => 'inv_0001',
                                // Without PaymentIntent
                                // 'payment_intent' => null,
                            ],
                        ],
                    ]));
                })
            )
        ;

        $action = new StatusSessionAction();
        $action->setGateway($gatewayMock);

        $model = [
            'object' => Session::OBJECT_NAME,
            'status' => Session::STATUS_COMPLETE,
            'payment_status' => Session::PAYMENT_STATUS_UNPAID,
            'subscription' => 'sub_0001',
        ];

        $request = new GetHumanStatus($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $this->assertTrue($request->isPending());
    }

    public function testShouldMarkAsCanceledIfIsASessionObjectIsCompletedAndPaymentStatusIsUnpaidInCaseOfASubscriptionWitInvoiceAndWithAPaymentIntentRequiresPaymentMethod(): void
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->exactly(3))
            ->method('execute')
            ->withConsecutive(
                [$this->isInstanceOf(Sync::class)],
                [$this->isInstanceOf(AllInvoice::class)],
                [$this->isInstanceOf(RetrievePaymentIntent::class)]
            )
            ->willReturnOnConsecutiveCalls(
                null,
                $this->returnCallback(function (AllInvoice $request) {
                    $request->setApiResources(Collection::constructFrom([
                        'data' => [
                            [
                                'model' => Invoice::OBJECT_NAME,
                                'id' => 'inv_0001',
                                'payment_intent' => 'pi_0001',
                            ],
                        ],
                    ]));
                }),
                $this->returnCallback(function (RetrievePaymentIntent $request) {
                    $request->setApiResource(PaymentIntent::constructFrom(
                        [
                            'model' => PaymentIntent::OBJECT_NAME,
                            'id' => 'pi_0001',
                            'status' => PaymentIntent::STATUS_REQUIRES_PAYMENT_METHOD,
                        ]
                    ));
                })
            )
        ;

        $action = new StatusSessionAction();
        $action->setGateway($gatewayMock);

        $model = [
            'object' => Session::OBJECT_NAME,
            'status' => Session::STATUS_COMPLETE,
            'payment_status' => Session::PAYMENT_STATUS_UNPAID,
            'subscription' => 'sub_0001',
        ];

        $request = new GetHumanStatus($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $this->assertTrue($request->isCanceled());
    }

    public function testShouldMarkAsCanceledIfIsASessionObjectIsCompletedAndPaymentStatusIsUnpaidInCaseOfASubscriptionWitInvoiceAndWithAPaymentIntentCanceled(): void
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->exactly(3))
            ->method('execute')
            ->withConsecutive(
                [$this->isInstanceOf(Sync::class)],
                [$this->isInstanceOf(AllInvoice::class)],
                [$this->isInstanceOf(RetrievePaymentIntent::class)]
            )
            ->willReturnOnConsecutiveCalls(
                null,
                $this->returnCallback(function (AllInvoice $request) {
                    $request->setApiResources(Collection::constructFrom([
                        'data' => [
                            [
                                'model' => Invoice::OBJECT_NAME,
                                'id' => 'inv_0001',
                                'payment_intent' => 'pi_0001',
                            ],
                        ],
                    ]));
                }),
                $this->returnCallback(function (RetrievePaymentIntent $request) {
                    $request->setApiResource(PaymentIntent::constructFrom(
                        [
                            'model' => PaymentIntent::OBJECT_NAME,
                            'id' => 'pi_0001',
                            'status' => PaymentIntent::STATUS_CANCELED,
                        ]
                    ));
                })
            )
        ;

        $action = new StatusSessionAction();
        $action->setGateway($gatewayMock);

        $model = [
            'object' => Session::OBJECT_NAME,
            'status' => Session::STATUS_COMPLETE,
            'payment_status' => Session::PAYMENT_STATUS_UNPAID,
            'subscription' => 'sub_0001',
        ];

        $request = new GetHumanStatus($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $this->assertTrue($request->isCanceled());
    }

    public function testShouldMarkAsPendingIfIsASessionObjectIsCompletedAndPaymentStatusIsUnpaidInCaseOfASubscriptionWitInvoiceAndWithAPaymentIntentProcessing(): void
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->exactly(3))
            ->method('execute')
            ->withConsecutive(
                [$this->isInstanceOf(Sync::class)],
                [$this->isInstanceOf(AllInvoice::class)],
                [$this->isInstanceOf(RetrievePaymentIntent::class)]
            )
            ->willReturnOnConsecutiveCalls(
                null,
                $this->returnCallback(function (AllInvoice $request) {
                    $request->setApiResources(Collection::constructFrom([
                        'data' => [
                            [
                                'model' => Invoice::OBJECT_NAME,
                                'id' => 'inv_0001',
                                'payment_intent' => 'pi_0001',
                            ],
                        ],
                    ]));
                }),
                $this->returnCallback(function (RetrievePaymentIntent $request) {
                    $request->setApiResource(PaymentIntent::constructFrom(
                        [
                            'model' => PaymentIntent::OBJECT_NAME,
                            'id' => 'pi_0001',
                            'status' => PaymentIntent::STATUS_PROCESSING,
                        ]
                    ));
                })
            )
        ;

        $action = new StatusSessionAction();
        $action->setGateway($gatewayMock);

        $model = [
            'object' => Session::OBJECT_NAME,
            'status' => Session::STATUS_COMPLETE,
            'payment_status' => Session::PAYMENT_STATUS_UNPAID,
            'subscription' => 'sub_0001',
        ];

        $request = new GetHumanStatus($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $this->assertTrue($request->isPending());
    }

    public function testSyncIsBringingADifferentObject(): void
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->exactly(2))
            ->method('execute')
            ->willReturnOnConsecutiveCalls(
                $this->returnCallback(function (Sync $request) {
                    $model = ArrayObject::ensureArrayObject($request->getModel());
                    $model->exchangeArray([
                        'object' => PaymentIntent::OBJECT_NAME,
                        'status' => PaymentIntent::STATUS_CANCELED,
                    ]);
                }),
                null
            )
        ;

        $action = new StatusSessionAction();
        $action->setGateway($gatewayMock);

        // The model here have to be an object to be aware of the Sync exchangeArray
        // @todo maybe it's required to support only ArrayObject here
        $model = ArrayObject::ensureArrayObject([
            'object' => Session::OBJECT_NAME,
            'status' => Session::STATUS_OPEN,
            'payment_status' => Session::PAYMENT_STATUS_UNPAID,
            'payment_intent' => 'null',
        ]);

        $request = new GetHumanStatus($model);
        $initialStatus = $request->getValue();

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $this->assertEquals($initialStatus, $request->getValue());
    }
}
