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

        $this->assertTrue($action->supports(new Cancel(['capture_method' => 'automatic'])));
        $this->assertFalse($action->supports(new Cancel(['capture_method' => 'manual'])));
        $this->assertFalse($action->supports(new Cancel([])));
        $this->assertFalse($action->supports(new Cancel(null)));
        $this->assertFalse($action->supports(new Authorize(['capture_method' => 'manual'])));
    }

    public function testDoesNothingForNotACheckoutSession(): void
    {
        $request = new Cancel(['capture_method' => 'automatic', 'id' => 'pi_abc123']);
        $action = new CancelAction();

        $allSessionRequest = new AllSession(['payment_intent' => 'pi_abc123']);
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($allSessionRequest)
            ->willReturn(
                $this->returnCallback(
                    function (AllSession $request) {
                        $sessions = Collection::emptyCollection();
                        $request->setApiResources($sessions);
                        return true;
                    }
                )
            );

        $action->setGateway($gatewayMock);
        $action->execute($request);
    }

    public function testDoesNothingForNotAnOpenedCheckoutSession(): void
    {
        $request = new Cancel(['capture_method' => 'automatic', 'id' => 'pi_abc123']);
        $action = new CancelAction();

        $allSessionRequest = new AllSession(['payment_intent' => 'pi_abc123']);
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($allSessionRequest)
            ->willReturn(
                $this->returnCallback(
                    function (AllSession $request) {
                        $sessions = Collection::constructFrom(
                            [
                                'data' => [
                                    [
                                        'id' => 'cs_1',
                                        'object' => Session::OBJECT_NAME,
                                        'status' => Session::STATUS_COMPLETE,
                                    ],
                                ],
                            ]
                        );
                        $request->setApiResources($sessions);
                        return true;
                    }
                )
            );

        $action->setGateway($gatewayMock);
        $action->execute($request);
    }

    public function testExpiresAnOpenedCheckoutSession(): void
    {
        $request = new Cancel(['capture_method' => 'automatic', 'id' => 'pi_abc123']);
        $action = new CancelAction();

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->exactly(2))
            ->method('execute')
            ->withConsecutive(
                [
                    $this->callback(
                        function (AllSession $request): bool {
                            $this->assertSame(['payment_intent' => 'pi_abc123'], $request->getParameters());
                            return true;
                        }
                    ),
                ],
                [new ExpireSession('cs_1')]
            )
            ->willReturnOnConsecutiveCalls(
                $this->returnCallback(
                    function (AllSession $request) {
                        $sessions = Collection::constructFrom(
                            [
                                'data' => [
                                    [
                                        'id' => 'cs_1',
                                        'object' => Session::OBJECT_NAME,
                                        'status' => Session::STATUS_OPEN,
                                    ],
                                ],
                            ]
                        );
                        $request->setApiResources($sessions);
                        return true;
                    }
                ),
                $this->anything()
            );

        $action->setGateway($gatewayMock);
        $action->execute($request);
    }
}
