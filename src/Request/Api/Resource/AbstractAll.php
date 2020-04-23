<?php

declare(strict_types=1);

namespace Prometee\PayumStripe\Request\Api\Resource;

use ArrayObject;
use Payum\Core\Request\Generic;
use Stripe\ApiResource;
use Stripe\Collection;

abstract class AbstractAll extends Generic implements AllInterface
{
    use OptionsAwareTrait;

    /** @var ApiResource[]|Collection|null */
    protected $apiResources;

    /**
     * @param array $parameters
     * @param array $options
     */
    public function __construct(array $parameters = [], array $options = [])
    {
        parent::__construct($parameters);
        $this->setOptions($options);
    }

    /**
     * {@inheritDoc}
     */
    public function getParameters(): ?array
    {
        $model = $this->getModel();
        if ($model instanceof ArrayObject) {
            return $model->getArrayCopy();
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function setParameters(array $parameters): void
    {
        $this->setModel($parameters);
    }

    /**
     * {@inheritDoc}
     */
    public function getApiResources(): ?Collection
    {
        return $this->apiResources;
    }

    /**
     * {@inheritDoc}
     */
    public function setApiResources(Collection $apiResources): void
    {
        $this->apiResources = $apiResources;
    }
}
