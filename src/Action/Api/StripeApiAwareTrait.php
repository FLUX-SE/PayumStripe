<?php

declare(strict_types=1);

namespace Prometee\PayumStripeCheckoutSession\Action\Api;

use Payum\Core\ApiAwareTrait;
use Prometee\PayumStripeCheckoutSession\Api\Keys;
use Prometee\PayumStripeCheckoutSession\Api\KeysInterface;

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
