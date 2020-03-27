<?php

declare(strict_types=1);

namespace Prometee\PayumStripeCheckoutSession\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;

interface StripeApiActionInterface extends ActionInterface, ApiAwareInterface
{
}
