<?php

declare(strict_types=1);

namespace Tests\FluxSE\PayumStripe\Unit\Action;

use Payum\Core\GatewayInterface;
use PHPUnit\Framework\MockObject\MockObject;

trait GatewayAwareTestTrait
{
    /**
     * Returns a mock object for the specified class.
     *
     * @param string|string[] $originalClassName
     *
     * @psalm-template RealInstanceType of object
     *
     * @psalm-param class-string<RealInstanceType>|string[] $originalClassName
     *
     * @psalm-return MockObject&RealInstanceType
     */
    abstract protected function createMock(string $originalClassName): MockObject;

    /**
     * @return MockObject&GatewayInterface
     */
    protected function createGatewayMock(): GatewayInterface
    {
        return $this->createMock(GatewayInterface::class);
    }
}
