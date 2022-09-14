<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Request\Api\Resource;

use Payum\Core\Request\Generic;

abstract class AbstractUpdate extends Generic implements UpdateInterface
{
    use OptionsAwareTrait;
    use ResourceAwareTrait;

    /** @var array */
    protected $parameters = [];

    public function __construct(string $id, array $parameters, array $options = [])
    {
        parent::__construct($id);
        $this->setParameters($parameters);
        $this->setOptions($options);
    }

    public function getId(): string
    {
        /** @var string $model */
        $model = $this->getModel();
        return $model;
    }

    public function setId(string $id): void
    {
        $this->setModel($id);
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }
}
