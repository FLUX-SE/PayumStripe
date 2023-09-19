<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api;

use FluxSE\PayumStripe\Api\StripeClientAwareInterface;
use Payum\Core\ApiAwareTrait;

/**
 * @property StripeClientAwareInterface $api
 */
trait StripeApiAwareTrait
{
    use ApiAwareTrait;

    public function __construct()
    {
        $this->initApiClass();
    }

    protected function initApiClass(): void
    {
        $this->apiClass = StripeClientAwareInterface::class;
    }

    /**
     * Use for tests and also if someone need to change the default Keys class.
     */
    public function setApiClass(string $apiClass): void
    {
        $this->apiClass = $apiClass;
    }

    /**
     * Use for tests and also if someone need to change the default Keys class.
     */
    public function getApiClass(): string
    {
        return $this->apiClass;
    }
}
