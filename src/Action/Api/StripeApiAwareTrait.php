<?php

declare(strict_types=1);

namespace Prometee\PayumStripeCheckoutSession\Action\Api;

use Payum\Core\ApiAwareTrait;
use Prometee\PayumStripeCheckoutSession\Api\Keys;

/**
 * @property Keys $api
 */
trait StripeApiAwareTrait
{
    use ApiAwareTrait;

    public function __construct()
    {
        $this->apiClass = Keys::class;
    }
}
