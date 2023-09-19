<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Stripe\Service\AbstractService;
use Stripe\StripeClient;

interface ResourceActionInterface extends ActionInterface, ApiAwareInterface
{
    public function getStripeService(StripeClient $stripeClient): AbstractService;
}
