<?php

declare(strict_types=1);

namespace Prometee\PayumStripeCheckoutSession\Action\Api\Resource;

use Payum\Core\Action\ActionInterface as BaseActionInterface;
use Payum\Core\ApiAwareInterface;

interface ResourceActionInterface extends BaseActionInterface, ApiAwareInterface
{
    /**
     * @return string
     */
    public function getApiResourceClass(): string;
}
