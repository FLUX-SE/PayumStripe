<?php

declare(strict_types=1);

namespace Prometee\PayumStripe\Request\Api\Resource;

use Stripe\ApiResource;

interface ResourceAwareInterface
{
    /**
     * @return ApiResource
     */
    public function getApiResource(): ApiResource;

    /**
     * @param ApiResource $apiResource
     */
    public function setApiResource(ApiResource $apiResource): void;
}
