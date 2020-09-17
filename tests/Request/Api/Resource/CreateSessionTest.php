<?php

namespace Tests\FluxSE\PayumStripe\Request\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\AbstractCreate;
use FluxSE\PayumStripe\Request\Api\Resource\CreateInterface;
use FluxSE\PayumStripe\Request\Api\Resource\CreateSession;
use FluxSE\PayumStripe\Request\Api\Resource\OptionsAwareInterface;
use FluxSE\PayumStripe\Request\Api\Resource\ResourceAwareInterface;
use Payum\Core\Request\Generic;
use PHPUnit\Framework\TestCase;
use Stripe\Checkout\Session;

final class CreateSessionTest extends TestCase
{
    /**
     * @test
     */
    public function shouldBeInstanceClassOfAbstractCreateAndCreateInterfaceAndOptionsAwareInterfaceAndGeneric()
    {
        $model = [];
        $createSession = new CreateSession($model);

        $this->assertInstanceOf(AbstractCreate::class, $createSession);
        $this->assertInstanceOf(CreateInterface::class, $createSession);
        $this->assertInstanceOf(OptionsAwareInterface::class, $createSession);
        $this->assertInstanceOf(ResourceAwareInterface::class, $createSession);
        $this->assertInstanceOf(Generic::class, $createSession);
    }

    public function testOptions()
    {
        $model = [];
        $createSession = new CreateSession($model, ['test' => 'test']);

        $this->assertEquals(['test' => 'test'], $createSession->getOptions());
        $createSession->setOptions([]);
        $this->assertEquals([], $createSession->getOptions());
    }

    public function testApiResource()
    {
        $model = [];
        $createSession = new CreateSession($model);

        $session = new Session();
        $createSession->setApiResource($session);

        $this->assertEquals($session, $createSession->getApiResource());
    }
}
