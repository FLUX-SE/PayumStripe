<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\RetrieveInterface;
use Stripe\ApiResource;

interface RetrieveActionInterface extends ResourceActionInterface
{
    /**
     * @param RetrieveInterface $request
     *
     * @return ApiResource
     */
    public function retrieveApiResource(RetrieveInterface $request): ApiResource;

    /**
     * @param RetrieveInterface $request
     *
     * @return bool
     */
    public function supportAlso(RetrieveInterface $request): bool;
}
