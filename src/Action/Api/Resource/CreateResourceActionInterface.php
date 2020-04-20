<?php

declare(strict_types=1);

namespace Prometee\PayumStripeCheckoutSession\Action\Api\Resource;

use Prometee\PayumStripeCheckoutSession\Request\Api\Resource\CreateInterface;
use Stripe\ApiResource;

interface CreateResourceActionInterface extends ResourceActionInterface
{
    /**
     * @param CreateInterface $request
     *
     * @return ApiResource
     */
    public function createApiResource(CreateInterface $request): ApiResource;

    /**
     * @param CreateInterface $request
     *
     * @return bool
     */
    public function supportAlso(CreateInterface $request): bool;
}
