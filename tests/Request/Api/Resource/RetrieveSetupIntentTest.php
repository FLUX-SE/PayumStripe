<?php

namespace Tests\FluxSE\PayumStripe\Request\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\AbstractRetrieve;
use FluxSE\PayumStripe\Request\Api\Resource\OptionsAwareInterface;
use FluxSE\PayumStripe\Request\Api\Resource\ResourceAwareInterface;
use FluxSE\PayumStripe\Request\Api\Resource\RetrieveInterface;
use FluxSE\PayumStripe\Request\Api\Resource\RetrieveSetupIntent;
use Payum\Core\Request\Generic;
use PHPUnit\Framework\TestCase;
use Stripe\SetupIntent;

class RetrieveSetupIntentTest extends TestCase
{
    /**
     * @test
     */
    public function shouldBeInstanceClassOfAbstractRetrieveAndRetrieveInterfaceAndOptionsAwareInterfaceAndGeneric()
    {
        $retrieveSetupIntent = new RetrieveSetupIntent('');

        $this->assertInstanceOf(AbstractRetrieve::class, $retrieveSetupIntent);
        $this->assertInstanceOf(RetrieveInterface::class, $retrieveSetupIntent);
        $this->assertInstanceOf(OptionsAwareInterface::class, $retrieveSetupIntent);
        $this->assertInstanceOf(ResourceAwareInterface::class, $retrieveSetupIntent);
        $this->assertInstanceOf(Generic::class, $retrieveSetupIntent);
    }

    public function testOptions()
    {
        $retrieveSetupIntent = new RetrieveSetupIntent('', ['test' => 'test']);

        $this->assertEquals(['test' => 'test'], $retrieveSetupIntent->getOptions());
        $retrieveSetupIntent->setOptions([]);
        $this->assertEquals([], $retrieveSetupIntent->getOptions());
    }

    public function testApiResource()
    {
        $retrieveSetupIntent = new RetrieveSetupIntent('');

        $setupIntent = new SetupIntent();
        $retrieveSetupIntent->setApiResource($setupIntent);

        $this->assertEquals($setupIntent, $retrieveSetupIntent->getApiResource());
    }
}
