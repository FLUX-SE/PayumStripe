<?php

declare(strict_types=1);

namespace Prometee\PayumStripeCheckoutSession\Request\Api\Resource;

use Payum\Core\Request\Generic;

abstract class AbstractUpdate extends Generic implements UpdateInterface
{
    use OptionsAwareTrait,
        ResourceAwareTrait;

    /** @var array */
    protected $parameters = [];

    /**
     * @param string $id
     * @param array $parameters
     * @param array $options
     */
    public function __construct(string $id, array $parameters, array $options = [])
    {
        parent::__construct($id);
        $this->setParameters($parameters);
        $this->setOptions($options);
    }

    /**
     * {@inheritDoc}
     */
    public function getId(): string
    {
        return (string) $this->getModel();
    }

    /**
     * {@inheritDoc}
     */
    public function setId(string $id): void
    {
        $this->setModel($id);
    }

    /**
     *
     * {@inheritDoc}
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * {@inheritDoc}
     */
    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }
}
