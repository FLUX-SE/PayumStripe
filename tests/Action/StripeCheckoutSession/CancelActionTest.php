<?php

namespace Tests\FluxSE\PayumStripe\Action\StripeCheckoutSession;

use FluxSE\PayumStripe\Action\StripeCheckoutSession\CancelAction;
use PHPUnit\Framework\TestCase;
use Tests\FluxSE\PayumStripe\Action\GatewayAwareTestTrait;

final class CancelActionTest extends TestCase
{
    use GatewayAwareTestTrait;
}
