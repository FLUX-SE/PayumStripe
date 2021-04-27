<?php

declare(strict_types=1);

namespace Tests\FluxSE\PayumStripe\Action\Api;

use FluxSE\PayumStripe\Api\KeysAwareInterface;
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
     * @return MockObject&KeysAwareInterface
     */
    protected function createApiMock(bool $shouldGetSecretKey = true): KeysAwareInterface
    {
        $apiMock = $this->createMock($this->getApiClass());

        if ($shouldGetSecretKey) {
            $apiMock
                ->expects($this->atLeastOnce())
                ->method('getSecretKey')
                ->willReturn('sk_test_123')
            ;
        }

        return $apiMock;
    }

    protected function getApiClass(): string
    {
        return KeysAwareInterface::class;
    }
}
