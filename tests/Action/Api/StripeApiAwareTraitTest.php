<?php

declare(strict_types=1);

namespace Tests\FluxSE\PayumStripe\Action\Api;

use FluxSE\PayumStripe\Action\Api\StripeApiAwareTrait;
use FluxSE\PayumStripe\Api\StripeClientAwareInterface;
use FluxSE\PayumStripe\Api\StripeClientAwareTrait;
use PHPUnit\Framework\TestCase;

final class StripeApiAwareTraitTest extends TestCase
{
    public function testShouldGetApiClass(): void
    {
        $trait = $this->getObjectForTrait(StripeApiAwareTrait::class);
        $this->assertEquals(StripeClientAwareInterface::class, $trait->getApiClass());
    }

    public function testShouldSetApiClass(): void
    {
        $trait = $this->getObjectForTrait(StripeApiAwareTrait::class);
        $trait->setApiClass(StripeClientAwareTrait::class);
        $this->assertEquals(StripeClientAwareTrait::class, $trait->getApiClass());
    }
}
