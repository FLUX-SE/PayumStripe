<?php

declare(strict_types=1);

namespace Tests\FluxSE\PayumStripe\Request\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\AbstractDelete;
use FluxSE\PayumStripe\Request\Api\Resource\DeleteInterface;
use FluxSE\PayumStripe\Request\Api\Resource\DeletePlan;
use FluxSE\PayumStripe\Request\Api\Resource\OptionsAwareInterface;
use FluxSE\PayumStripe\Request\Api\Resource\ResourceAwareInterface;
use LogicException;
use Payum\Core\Request\Generic;
use PHPUnit\Framework\TestCase;
use Stripe\ApiOperations\Delete;
use Stripe\ApiResource;
use Stripe\Plan;

final class DeleteTest extends TestCase
{
    /**
     * @dataProvider requestList
     */
    public function testShouldBeInstanceOf(string $deleteRequestClass)
    {
        /** @var AbstractDelete $deleteRequest */
        $deleteRequest = new $deleteRequestClass('');

        $this->assertInstanceOf(AbstractDelete::class, $deleteRequest);
        $this->assertInstanceOf(DeleteInterface::class, $deleteRequest);
        $this->assertInstanceOf(OptionsAwareInterface::class, $deleteRequest);
        $this->assertInstanceOf(ResourceAwareInterface::class, $deleteRequest);
        $this->assertInstanceOf(Generic::class, $deleteRequest);
    }

    /**
     * @dataProvider requestList
     */
    public function testGetId(string $deleteRequestClass)
    {
        /** @var AbstractDelete $deleteRequest */
        $deleteRequest = new $deleteRequestClass('');
        $this->assertEquals('', $deleteRequest->getId());
    }

    /**
     * @dataProvider requestList
     */
    public function testSetId(string $deleteRequestClass)
    {
        /** @var AbstractDelete $deleteRequest */
        $deleteRequest = new $deleteRequestClass('');
        $id = 'delete_1';
        $deleteRequest->setId($id);
        $this->assertEquals($id, $deleteRequest->getId());
    }

    /**
     * @dataProvider requestList
     */
    public function testOptions(string $deleteRequestClass)
    {
        $options = ['test' => 'test'];
        /** @var AbstractDelete $deleteRequest */
        $deleteRequest = new $deleteRequestClass('', $options);
        $this->assertEquals($options, $deleteRequest->getOptions());

        $options = [];
        $deleteRequest->setOptions($options);
        $this->assertEquals($options, $deleteRequest->getOptions());
    }

    /**
     * @dataProvider requestList
     */
    public function testApiResource(string $deleteRequestClass, string $deleteClass)
    {
        /** @var AbstractDelete $deleteRequest */
        $deleteRequest = new $deleteRequestClass('');

        /** @var Delete&ApiResource $delete */
        $delete = new $deleteClass();
        $deleteRequest->setApiResource($delete);
        $this->assertEquals($delete, $deleteRequest->getApiResource());
    }

    /**
     * @dataProvider requestList
     */
    public function testNullApiResource(string $deleteRequestClass)
    {
        /** @var AbstractDelete $deleteRequest */
        $deleteRequest = new $deleteRequestClass('');
        $this->expectException(LogicException::class);
        $deleteRequest->getApiResource();
    }

    public function requestList(): array
    {
        return [
            [DeletePlan::class, Plan::class],
        ];
    }
}
