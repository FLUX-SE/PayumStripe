<?php

namespace Tests\Prometee\PayumStripeCheckoutSession\Request;

use Payum\Core\Model\Token;
use Payum\Core\Request\Generic;
use PHPUnit\Framework\TestCase;
use Prometee\PayumStripeCheckoutSession\Request\DeleteWebhookToken;

class DeleteWebhookTokenTest extends TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfGeneric()
    {
        $deleteWebhookToken = new DeleteWebhookToken(new Token());

        $this->assertInstanceOf(Generic::class, $deleteWebhookToken);
    }
}
