<?php

declare(strict_types=1);

namespace Tests\FluxSE\PayumStripe\Request\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\AbstractUpdate;
use FluxSE\PayumStripe\Request\Api\Resource\OptionsAwareInterface;
use FluxSE\PayumStripe\Request\Api\Resource\ResourceAwareInterface;
use FluxSE\PayumStripe\Request\Api\Resource\UpdateInterface;
use FluxSE\PayumStripe\Request\Api\Resource\UpdateSubscription;
use LogicException;
use Payum\Core\Request\Generic;
use PHPUnit\Framework\TestCase;
use Stripe\ApiOperations\Update;
use Stripe\ApiResource;
use Stripe\Subscription;

final class UpdateTest extends TestCase
{
    /**
     * @dataProvider requestList
     */
    public function testShouldBeInstanceOf(string $updateRequestClass)
    {
        /** @var AbstractUpdate $updateRequest */
        $updateRequest = new $updateRequestClass('', []);

        $this->assertInstanceOf(AbstractUpdate::class, $updateRequest);
        $this->assertInstanceOf(UpdateInterface::class, $updateRequest);
        $this->assertInstanceOf(OptionsAwareInterface::class, $updateRequest);
        $this->assertInstanceOf(ResourceAwareInterface::class, $updateRequest);
        $this->assertInstanceOf(Generic::class, $updateRequest);
    }

    /**
     * @dataProvider requestList
     */
    public function testGetId(string $updateRequestClass)
    {
        /** @var AbstractUpdate $updateRequest */
        $updateRequest = new $updateRequestClass('', []);
        $this->assertEquals('', $updateRequest->getId());
    }

    /**
     * @dataProvider requestList
     */
    public function testSetId(string $updateRequestClass)
    {
        /** @var AbstractUpdate $updateRequest */
        $updateRequest = new $updateRequestClass('', []);
        $id = 'update_1';
        $updateRequest->setId($id);
        $this->assertEquals($id, $updateRequest->getId());
    }

    /**
     * @dataProvider requestList
     */
    public function testOptions(string $updateRequestClass)
    {
        $options = ['test' => 'test'];
        /** @var AbstractUpdate $updateRequest */
        $updateRequest = new $updateRequestClass('', [], $options);
        $this->assertEquals($options, $updateRequest->getOptions());

        $options = [];
        $updateRequest->setOptions($options);
        $this->assertEquals($options, $updateRequest->getOptions());
    }

    /**
     * @dataProvider requestList
     */
    public function testApiResource(string $updateRequestClass, string $updateClass)
    {
        /** @var AbstractUpdate $updateRequest */
        $updateRequest = new $updateRequestClass('', []);

        /** @var Update&ApiResource $update */
        $update = new $updateClass();
        $updateRequest->setApiResource($update);
        $this->assertEquals($update, $updateRequest->getApiResource());
    }

    /**
     * @dataProvider requestList
     */
    public function testNullApiResource(string $updateRequestClass)
    {
        /** @var AbstractUpdate $updateRequest */
        $updateRequest = new $updateRequestClass('', []);
        $this->expectException(LogicException::class);
        $updateRequest->getApiResource();
    }

    /**
     * @dataProvider requestList
     */
    public function testGetSetParameters(string $updateRequestClass)
    {
        $parameters = ['field' => 'value'];
        /** @var AbstractUpdate $updateRequest */
        $updateRequest = new $updateRequestClass('', []);
        $updateRequest->setParameters($parameters);
        $this->assertEquals($parameters, $updateRequest->getParameters());
    }

    public function requestList(): array
    {
        return [
            [UpdateSubscription::class, Subscription::class],
        ];
    }
}
