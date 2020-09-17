<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;

interface ResourceActionInterface extends ActionInterface, ApiAwareInterface
{
    public function getApiResourceClass(): string;

    public function setApiResourceClass(string $apiResourceClass): void;
}
