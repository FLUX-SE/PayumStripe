<?php

declare(strict_types=1);

namespace Tests\FluxSE\PayumStripe\Action\Api;

use FluxSE\PayumStripe\Action\Api\StripeApiAwareTrait;
use FluxSE\PayumStripe\Api\Keys;
use FluxSE\PayumStripe\Api\KeysInterface;
use PHPUnit\Framework\TestCase;

final class StripeApiAwareTraitTest extends TestCase
{
    public function testShouldGetApiClass()
    {
        $trait = $this->getObjectForTrait(StripeApiAwareTrait::class);
        $this->assertEquals(Keys::class, $trait->getApiClass());
    }

    public function testShouldSetApiClass()
    {
        $trait = $this->getObjectForTrait(StripeApiAwareTrait::class);
        $trait->setApiClass(KeysInterface::class);
        $this->assertEquals(KeysInterface::class, $trait->getApiClass());
    }
}
