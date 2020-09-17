<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Request\Api\Resource;

use Payum\Core\Model\ModelAggregateInterface;
use Payum\Core\Model\ModelAwareInterface;
use Payum\Core\Security\TokenAggregateInterface;

interface RetrieveInterface extends ResourceAwareInterface, OptionsAwareInterface, ModelAwareInterface, ModelAggregateInterface, TokenAggregateInterface
{
    public function getId(): string;

    public function setId(string $id): void;
}
