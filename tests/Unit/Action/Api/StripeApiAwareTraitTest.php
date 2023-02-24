<?php

declare(strict_types=1);

namespace Tests\FluxSE\PayumStripe\Unit\Action\Api;

use FluxSE\PayumStripe\Action\Api\StripeApiAwareTrait;
use FluxSE\PayumStripe\Api\KeysAwareInterface;
use FluxSE\PayumStripe\Api\KeysAwareTrait;
use PHPUnit\Framework\TestCase;

final class StripeApiAwareTraitTest extends TestCase
{
    public function testShouldGetApiClass(): void
    {
        $trait = $this->getObjectForTrait(StripeApiAwareTrait::class);
        $this->assertEquals(KeysAwareInterface::class, $trait->getApiClass());
    }

    public function testShouldSetApiClass(): void
    {
        $trait = $this->getObjectForTrait(StripeApiAwareTrait::class);
        $trait->setApiClass(KeysAwareTrait::class);
        $this->assertEquals(KeysAwareTrait::class, $trait->getApiClass());
    }
}
