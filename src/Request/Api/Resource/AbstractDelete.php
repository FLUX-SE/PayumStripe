<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Request\Api\Resource;

abstract class AbstractDelete extends AbstractRetrieve implements DeleteInterface
{
    use OptionsAwareTrait;
    use ResourceAwareTrait;

    public function __construct(string $id, array $options = [])
    {
        parent::__construct($id);
        $this->setOptions($options);
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): string
    {
        return (string) $this->getModel();
    }

    /**
     * {@inheritdoc}
     */
    public function setId(string $id): void
    {
        $this->setModel($id);
    }
}
