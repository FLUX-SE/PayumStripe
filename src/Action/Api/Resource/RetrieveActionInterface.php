<?php

declare(strict_types=1);

namespace Prometee\PayumStripeCheckoutSession\Action\Api\Resource;

use Prometee\PayumStripeCheckoutSession\Request\Api\Resource\RetrieveInterface;
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
