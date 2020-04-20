<?php

declare(strict_types=1);

namespace Prometee\PayumStripeCheckoutSession\Request\Api\Resource;

use ArrayObject;
use Payum\Core\Request\Generic;

abstract class AbstractCreate extends Generic implements CreateInterface
{
    use OptionsAwareTrait,
        ResourceAwareTrait;

    /**
     * @param ArrayObject $model
     * @param array $options
     */
    public function __construct(ArrayObject $model, array $options = [])
    {
        parent::__construct($model);
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
}
