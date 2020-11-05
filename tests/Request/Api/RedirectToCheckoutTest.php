<?php

namespace Tests\FluxSE\PayumStripe\Request\Api;

use FluxSE\PayumStripe\Request\Api\RedirectToCheckout;
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
