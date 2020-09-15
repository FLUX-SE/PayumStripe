<?php

namespace Tests\FluxSE\PayumStripe\Request\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\AbstractRetrieve;
use FluxSE\PayumStripe\Request\Api\Resource\OptionsAwareInterface;
use FluxSE\PayumStripe\Request\Api\Resource\ResourceAwareInterface;
use FluxSE\PayumStripe\Request\Api\Resource\RetrieveInterface;
use FluxSE\PayumStripe\Request\Api\Resource\RetrieveSession;
use Payum\Core\Request\Generic;
use PHPUnit\Framework\TestCase;
use Stripe\Checkout\Session;

class RetrieveSessionTest extends TestCase
{
    /**
     * @test
     */
    public function shouldBeInstanceClassOfAbstractRetrieveAndRetrieveInterfaceAndOptionsAwareInterfaceAndGeneric()
    {
        $retrieveSession = new RetrieveSession('');

        $this->assertInstanceOf(AbstractRetrieve::class, $retrieveSession);
        $this->assertInstanceOf(RetrieveInterface::class, $retrieveSession);
        $this->assertInstanceOf(OptionsAwareInterface::class, $retrieveSession);
        $this->assertInstanceOf(ResourceAwareInterface::class, $retrieveSession);
        $this->assertInstanceOf(Generic::class, $retrieveSession);
    }

    public function testOptions()
    {
        $retrieveSession = new RetrieveSession('', ['test' => 'test']);

        $this->assertEquals(['test' => 'test'], $retrieveSession->getOptions());
        $retrieveSession->setOptions([]);
        $this->assertEquals([], $retrieveSession->getOptions());
    }

    public function testApiResource()
    {
        $retrieveSession = new RetrieveSession('');

        $session = new Session();
        $retrieveSession->setApiResource($session);

        $this->assertEquals($session, $retrieveSession->getApiResource());
    }
}
