<?php

declare(strict_types=1);

namespace Prometee\PayumStripeCheckoutSession\Request\Api;

use Payum\Core\Request\Generic;

final class RedirectToCheckout extends Generic
{
    /**
     * @param array $model
     */
    public function __construct(array $model)
    {
        parent::__construct($model);
    }
}
