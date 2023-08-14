<?php

namespace Tests\FluxSE\PayumStripe\Unit\Action\Api;

use FluxSE\PayumStripe\Action\Api\ConstructEventAction;
use FluxSE\PayumStripe\Request\Api\ConstructEvent;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayInterface;
use PHPUnit\Framework\TestCase;
use Stripe\Exception\SignatureVerificationException;

final class ConstructEventActionTest extends TestCase
{
    public function testShouldImplements(): void
    {
        $action = new ConstructEventAction();

        $this->assertInstanceOf(ActionInterface::class, $action);
        $this->assertNotInstanceOf(ApiAwareInterface::class, $action);
        $this->assertNotInstanceOf(GatewayInterface::class, $action);
    }

    public function testShouldThrowExceptionWhenInvalidPayloadIsRequested(): void
    {
        $payload = '';
        $sigHeader = '';
        $webhookSecretKey = '';

        $action = new ConstructEventAction();

        $request = new ConstructEvent($payload, $sigHeader, $webhookSecretKey);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $this->expectException(SignatureVerificationException::class);
        $action->execute($request);

        $this->assertNull($request->getEventWrapper());
        $this->assertNull($request->getWebhookSecretKey());
    }

    public function testShouldGetAnEventWrapperWhenValidPayloadIsRequested(): void
    {
        $now = time();

        $webhookSecretKey = 'whsec_test';

        $payload = file_get_contents(__DIR__ . '/../../Resources/Webhooks/checkout-session-completed.json');

        $signedPayload = sprintf('%s.%s', $now, $payload);
        $signature = hash_hmac('sha256', $signedPayload, $webhookSecretKey);

        $sigHeader = sprintf('t=%s,', $now);
        $sigHeader .= sprintf('v1=%s,', $signature);
        // Useless but here to tests legacy too
        $sigHeader .= 'v0=6ffbb59b2300aae63f272406069a9788598b792a944a07aba816edb039989a39';

        $action = new ConstructEventAction();

        $request = new ConstructEvent($payload, $sigHeader, $webhookSecretKey);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $this->assertNotNull($request->getEventWrapper());
        $this->assertNotNull($request->getWebhookSecretKey());
    }
}
