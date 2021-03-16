<?php

namespace Tests\FluxSE\PayumStripe\Request\StripeCheckoutSession\Api;

use FluxSE\PayumStripe\Request\StripeCheckoutSession\Api\RedirectToCheckout;
use Payum\Core\Request\Generic;
use PHPUnit\Framework\TestCase;

final class RedirectToCheckoutTest extends TestCase
{
    public function testShouldBeSubClassOfGeneric()
    {
        $redirectToCheckout = new RedirectToCheckout([]);

        $this->assertInstanceOf(Generic::class, $redirectToCheckout);
    }
}
