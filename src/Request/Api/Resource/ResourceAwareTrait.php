<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Request\Api\Resource;

use LogicException;
use Stripe\ApiResource;

trait ResourceAwareTrait
{
    /**
     * @var ApiResource|null
     */
    protected $apiResource;

    /**
     * {@inheritdoc}
     *
     * @throws LogicException
     */
    public function getApiResource(): ApiResource
    {
        if (null === $this->apiResource) {
            throw new LogicException('The API Resource is null !'.'You should send this request to `Payum->execute($request)` before using this getter.');
        }

        return $this->apiResource;
    }

    /**
     * {@inheritdoc}
     */
    public function setApiResource(ApiResource $apiResource): void
    {
        $this->apiResource = $apiResource;
    }
}
