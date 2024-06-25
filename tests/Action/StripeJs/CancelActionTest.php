<?php

namespace Action\StripeJs;

use FluxSE\PayumStripe\Action\StripeJs\CancelAction;
use FluxSE\PayumStripe\Request\Api\Resource\AbstractCustomCall;
use FluxSE\PayumStripe\Request\Api\Resource\CancelPaymentIntent;
use FluxSE\PayumStripe\Request\Api\Resource\CancelSetupIntent;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Request\Cancel;
use PHPUnit\Framework\TestCase;
use Stripe\PaymentIntent;
use Stripe\SetupIntent;
use Tests\FluxSE\PayumStripe\Action\GatewayAwareTestTrait;

final class CancelActionTest extends TestCase
{
    use GatewayAwareTestTrait;

    public function testShouldImplements(): void
    {
        $action = new CancelAction();

        $this->assertInstanceOf(ActionInterface::class, $action);
    }

    public function testShouldSupportOnlyCaptureAndArrayAccessModelWithCaptureMethodAutomatic(): void
    {
        $action = new CancelAction();

        $this->assertTrue($action->supports(new Cancel(['object' => PaymentIntent::OBJECT_NAME])));
        $this->assertTrue($action->supports(new Cancel(['object' => SetupIntent::OBJECT_NAME])));
        $this->assertFalse($action->supports(new Cancel([])));
        $this->assertFalse($action->supports(new Cancel(['object' => ''])));
        $this->assertFalse($action->supports(new Cancel(['object' => 'foo'])));
        $this->assertFalse($action->supports(new Cancel(null)));
    }

    /**
     * @dataProvider getWrongStatus
     */
    public function testDoesNothingForWrongStatusPaymentIntent(string $objectName, string $status): void
    {
        $request = new Cancel([
            'object' => $objectName,
            'id' => 'pi_abc123',
            'status' => $status,
        ]);
        $action = new CancelAction();

        $gatewayMock = $this->createGatewayMock();

        $gatewayMock
            ->expects($this->never())
            ->method('execute')
        ;

        $action->setGateway($gatewayMock);
        $action->execute($request);
    }

    /**
     * @dataProvider getOkStatus
     */
    public function testCancelARequiresPaymentMethodPaymentIntent(string $objectName, string $status): void
    {
        $request = new Cancel([
            'object' => $objectName,
            'id' => 'pi_abc123',
            'status' => $status,
        ]);
        $action = new CancelAction();

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->willReturnCallback(function (AbstractCustomCall $request) use ($objectName): void {
                if ($objectName === PaymentIntent::OBJECT_NAME) {
                    $this->assertInstanceOf(CancelPaymentIntent::class, $request);
                }

                if ($objectName === SetupIntent::OBJECT_NAME) {
                    $this->assertInstanceOf(CancelSetupIntent::class, $request);
                }
            })
        ;

        $action->setGateway($gatewayMock);
        $action->execute($request);
    }

    public function getWrongStatus(): array
    {
        return [
            [PaymentIntent::OBJECT_NAME, PaymentIntent::STATUS_SUCCEEDED],
            [PaymentIntent::OBJECT_NAME, PaymentIntent::STATUS_CANCELED],
            [SetupIntent::OBJECT_NAME, SetupIntent::STATUS_SUCCEEDED],
            [SetupIntent::OBJECT_NAME, SetupIntent::STATUS_CANCELED],
        ];
    }

    public function getOkStatus(): array
    {
        return [
            [PaymentIntent::OBJECT_NAME, PaymentIntent::STATUS_PROCESSING],
            [PaymentIntent::OBJECT_NAME, PaymentIntent::STATUS_REQUIRES_ACTION],
            [PaymentIntent::OBJECT_NAME, PaymentIntent::STATUS_REQUIRES_CAPTURE],
            [PaymentIntent::OBJECT_NAME, PaymentIntent::STATUS_REQUIRES_CONFIRMATION],
            [PaymentIntent::OBJECT_NAME, PaymentIntent::STATUS_REQUIRES_PAYMENT_METHOD],
            [SetupIntent::OBJECT_NAME, SetupIntent::STATUS_PROCESSING],
            [SetupIntent::OBJECT_NAME, SetupIntent::STATUS_REQUIRES_ACTION],
            [SetupIntent::OBJECT_NAME, SetupIntent::STATUS_REQUIRES_CONFIRMATION],
            [SetupIntent::OBJECT_NAME, SetupIntent::STATUS_REQUIRES_PAYMENT_METHOD],
        ];
    }
}
