<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api;

use FluxSE\PayumStripe\Api\Keys;
use FluxSE\PayumStripe\Api\KeysInterface;
use Payum\Core\ApiAwareTrait;

/**
 * @property KeysInterface $api
 */
trait StripeApiAwareTrait
{
    use ApiAwareTrait;

    public function __construct()
    {
        $this->apiClass = Keys::class;
    }

    /**
     * Use for tests and also if someone need to change the default Keys class
     *
     * @param string $apiClass
     */
    public function setApiClass(string $apiClass): void
    {
        $this->apiClass = $apiClass;
    }

    /**
     * Use for tests and also if someone need to change the default Keys class
     *
     * @return string
     */
    public function getApiClass(): string
    {
        return $this->apiClass;
    }
}
