<?php

declare(strict_types=1);

namespace Prometee\PayumStripe\Action\Api\Resource;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;

interface ResourceActionInterface extends ActionInterface, ApiAwareInterface
{
    /**
     * @return string
     */
    public function getApiResourceClass(): string;

    /**
     * @param string $apiResourceClass
     */
    public function setApiResourceClass(string $apiResourceClass): void;
}
