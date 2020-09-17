<?php

declare(strict_types=1);

namespace Tests\FluxSE\PayumStripe\Action\Api;

use FluxSE\PayumStripe\Api\KeysInterface;
use PHPUnit\Framework\MockObject\MockObject;

trait ApiAwareActionTestTrait
{
    /**
     * Returns a mock object for the specified class.
     *
     * @psalm-template RealInstanceType of object
     * @psalm-param class-string<RealInstanceType> $originalClassName
     * @psalm-return MockObject&RealInstanceType
     */
    abstract protected function createMock(string $originalClassName): MockObject;

    /**
     * @return MockObject&KeysInterface
     */
    protected function createApiMock(bool $shouldGetSecretKey = true): KeysInterface
    {
        $apiMock = $this->createMock(KeysInterface::class);

        if ($shouldGetSecretKey) {
            $apiMock
                ->expects($this->atLeastOnce())
                ->method('getSecretKey')
                ->willReturn('')
            ;
        }

        return $apiMock;
    }
}
