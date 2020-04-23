<?php

declare(strict_types=1);

namespace Tests\Prometee\PayumStripe\Action\Api;

use PHPUnit\Framework\MockObject\MockObject;
use Prometee\PayumStripe\Api\KeysInterface;

trait ApiAwareActionTestTrait
{
    /**
     * Returns a mock object for the specified class.
     *
     * @param string|string[] $originalClassName
     *
     * @psalm-template RealInstanceType of object
     * @psalm-param class-string<RealInstanceType>|string[] $originalClassName
     * @psalm-return MockObject&RealInstanceType
     *
     * @return MockObject
     */
    abstract protected function createMock($originalClassName): MockObject;

    /**
     * @param bool $shouldGetSecretKey
     *
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
