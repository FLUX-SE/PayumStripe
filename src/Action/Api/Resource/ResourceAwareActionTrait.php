<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

trait ResourceAwareActionTrait
{
    /** @var string */
    protected $apiResourceClass = '';

    /**
     * {@inheritdoc}
     */
    public function getApiResourceClass(): string
    {
        return $this->apiResourceClass;
    }

    /**
     * {@inheritdoc}
     */
    public function setApiResourceClass(string $apiResourceClass): void
    {
        $this->apiResourceClass = $apiResourceClass;
    }
}
