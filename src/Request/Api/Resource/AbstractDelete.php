<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Request\Api\Resource;

abstract class AbstractDelete extends AbstractRetrieve implements DeleteInterface
{
    use OptionsAwareTrait,
        ResourceAwareTrait;

    /**
     * @param string $id
     * @param array $options
     */
    public function __construct(string $id, array $options = [])
    {
        parent::__construct($id);
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
}
