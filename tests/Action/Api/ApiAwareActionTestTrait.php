<?php

declare(strict_types=1);

namespace Tests\FluxSE\PayumStripe\Action\Api;

use FluxSE\PayumStripe\Api\StripeClientAwareInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Stripe\StripeClient;

trait ApiAwareActionTestTrait
{
    /**
     * Returns a mock object for the specified class.
     *
     * @psalm-template RealInstanceType of object
     *
     * @psalm-param class-string<RealInstanceType> $originalClassName
     *
     * @psalm-return MockObject&RealInstanceType
     */
    abstract protected function createMock(string $originalClassName): MockObject;

    /**
     * @return MockObject&StripeClientAwareInterface
     */
    protected function createApiMock(): StripeClientAwareInterface
    {
        $stripeClient = new StripeClient('sk_test_123');
        $apiMock = $this->createMock($this->getApiClass());

        $apiMock
            ->method('getStripeClient')
            ->willReturn($stripeClient)
        ;

        return $apiMock;
    }

    protected function getApiClass(): string
    {
        return StripeClientAwareInterface::class;
    }
}
