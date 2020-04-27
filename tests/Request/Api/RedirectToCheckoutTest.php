<?php

namespace Tests\Prometee\PayumStripe\Request\Api;

use Payum\Core\Request\Generic;
use PHPUnit\Framework\TestCase;
use Prometee\PayumStripe\Request\Api\RedirectToCheckout;

final class RedirectToCheckoutTest extends TestCase
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
