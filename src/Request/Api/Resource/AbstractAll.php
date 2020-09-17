<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Request\Api\Resource;

use ArrayObject;
use LogicException;
use Payum\Core\Request\Generic;
use Stripe\ApiResource;
use Stripe\Collection;

abstract class AbstractAll extends Generic implements AllInterface
{
    use OptionsAwareTrait;

    /** @var ApiResource[]|Collection|null */
    protected $apiResources;

    public function __construct(array $parameters = [], array $options = [])
    {
        parent::__construct($parameters);
        $this->setOptions($options);
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function setParameters(array $parameters): void
    {
        $this->setModel($parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function getApiResources(): Collection
    {
        if (null === $this->apiResources) {
            throw new LogicException('The API Resources is null !'.'You should send this request to `Payum->execute()` before using this getter.');
        }

        return $this->apiResources;
    }

    /**
     * {@inheritdoc}
     */
    public function setApiResources(Collection $apiResources): void
    {
        $this->apiResources = $apiResources;
    }
}
