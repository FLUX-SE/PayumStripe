<?php

namespace Tests\Prometee\PayumStripeCheckoutSession\Request\Api\Resource;

use ArrayObject;
use Payum\Core\Request\Generic;
use PHPUnit\Framework\TestCase;
use Prometee\PayumStripeCheckoutSession\Request\Api\Resource\AbstractCreate;
use Prometee\PayumStripeCheckoutSession\Request\Api\Resource\CreateInterface;
use Prometee\PayumStripeCheckoutSession\Request\Api\Resource\CreateSession;
use Prometee\PayumStripeCheckoutSession\Request\Api\Resource\OptionsAwareInterface;
use Prometee\PayumStripeCheckoutSession\Request\Api\Resource\ResourceAwareInterface;
use Stripe\Checkout\Session;

class CreateSessionTest extends TestCase
{
    /**
     * @test
     */
    public function shouldBeInstanceClassOfAbstractCreateAndCreateInterfaceAndOptionsAwareInterfaceAndGeneric()
    {
        $model = new ArrayObject([]);
        $createSession = new CreateSession($model);

        $this->assertInstanceOf(AbstractCreate::class, $createSession);
        $this->assertInstanceOf(CreateInterface::class, $createSession);
        $this->assertInstanceOf(OptionsAwareInterface::class, $createSession);
        $this->assertInstanceOf(ResourceAwareInterface::class, $createSession);
        $this->assertInstanceOf(Generic::class, $createSession);
    }

    public function testOptions()
    {
        $model = new ArrayObject([]);
        $createSession = new CreateSession($model, ['test' => 'test']);

        $this->assertEquals(['test' => 'test'], $createSession->getOptions());
        $createSession->setOptions([]);
        $this->assertEquals([], $createSession->getOptions());
    }

    public function testApiResource()
    {
        $model = new ArrayObject([]);
        $createSession = new CreateSession($model);

        $session = new Session();
        $createSession->setApiResource($session);

        $this->assertEquals($session, $createSession->getApiResource());
    }
}
