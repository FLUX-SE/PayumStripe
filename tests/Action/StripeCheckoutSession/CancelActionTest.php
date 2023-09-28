<?php

namespace Tests\FluxSE\PayumStripe\Action\StripeCheckoutSession;

use FluxSE\PayumStripe\Action\StripeCheckoutSession\CancelAction;
use FluxSE\PayumStripe\Request\Api\Resource\AllSession;
use FluxSE\PayumStripe\Request\Api\Resource\ExpireSession;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\Cancel;
use PHPUnit\Framework\TestCase;
use Stripe\Checkout\Session;
use Stripe\Collection;
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

        $this->assertTrue($action->supports(new Cancel(['object' => Session::OBJECT_NAME])));
        $this->assertFalse($action->supports(new Cancel([])));
        $this->assertFalse($action->supports(new Cancel(['object' => ''])));
        $this->assertFalse($action->supports(new Cancel(['object' => 'foo'])));
        $this->assertFalse($action->supports(new Cancel(null)));
    }

    public function testDoesNothingForNotAnOpenedCheckoutSession(): void
    {
        $request = new Cancel([
            'object' => Session::OBJECT_NAME,
            'id' => 'cs_abc123',
            'status' => Session::STATUS_COMPLETE,
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

    public function testExpiresAnOpenedCheckoutSession(): void
    {
        $request = new Cancel([
            'object' => Session::OBJECT_NAME,
            'id' => 'pi_abc123',
            'status' => Session::STATUS_OPEN,
        ]);
        $action = new CancelAction();

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->willReturnCallback(function (ExpireSession $request): void {
                return;
            })
        ;

        $action->setGateway($gatewayMock);
        $action->execute($request);
    }
}
