<?php

declare(strict_types=1);

namespace Prometee\PayumStripeCheckoutSession\Request\Api\Resource;

use Payum\Core\Model\ModelAggregateInterface;
use Payum\Core\Model\ModelAwareInterface;
use Payum\Core\Security\TokenAggregateInterface;

interface UpdateInterface extends ResourceAwareInterface, OptionsAwareInterface, ModelAwareInterface, ModelAggregateInterface, TokenAggregateInterface
{
    /**
     * @return string
     */
    public function getId(): string;

    /**
     * @param string $id
     */
    public function setId(string $id): void;

    /**
     * @return array
     */
    public function getParameters(): array;

    /**
     * @param array $parameters
     */
    public function setParameters(array $parameters): void;
}
