<?php

namespace Tests\Prometee\PayumStripeCheckoutSession\Request\Api;

use Payum\Core\Request\Generic;
use PHPUnit\Framework\TestCase;
use Prometee\PayumStripeCheckoutSession\Request\Api\RedirectToCheckout;

class RedirectToCheckoutTest extends TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfGeneric()
    {
        $redirectToCheckout = new RedirectToCheckout([]);

        $this->assertInstanceOf(Generic::class, $redirectToCheckout);
    }
}
