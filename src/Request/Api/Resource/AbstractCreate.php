<?php

declare(strict_types=1);

namespace Prometee\PayumStripe\Request\Api\Resource;

use ArrayObject;
use LogicException;
use Payum\Core\Request\Generic;

abstract class AbstractCreate extends Generic implements CreateInterface
{
    use OptionsAwareTrait,
        ResourceAwareTrait;

    /**
     * @param array $model
     * @param array $options
     */
    public function __construct(array $model, array $options = [])
    {
        parent::__construct($model);
        $this->setOptions($options);
    }

    /**
     * {@inheritDoc}
     */
    public function getParameters(): array
    {
        $model = $this->getModel();
        if ($model instanceof ArrayObject) {
            return $model->getArrayCopy();
        }

        throw new LogicException(sprintf(
            'The parameter is null or is not an instance of %s !',
            ArrayObject::class
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function setParameters(array $parameters): void
    {
        $this->setModel($parameters);
    }
}
