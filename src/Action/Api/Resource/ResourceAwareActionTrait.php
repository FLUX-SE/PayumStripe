<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Api\StripeClientAwareInterface;
use Payum\Core\Exception\LogicException;
use Stripe\Service\AbstractService;
use Stripe\StripeClient;

/**
 * @property StripeClientAwareInterface $api
 */
trait ResourceAwareActionTrait
{
    abstract public function getStripeService(StripeClient $stripeClient): AbstractService;

    protected function getService(): AbstractService
    {
        if (null === $this->api) {
            throw new LogicException('Api class not found !');
        }

        return $this->getStripeService($this->api->getStripeClient());
    }
}
