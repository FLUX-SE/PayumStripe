<?php

declare(strict_types=1);

namespace Prometee\PayumStripeCheckoutSession\Action\Api\Resource;

use Prometee\PayumStripeCheckoutSession\Request\Api\Resource\UpdateInterface;
use Stripe\ApiResource;

interface UpdateResourceActionInterface extends ResourceActionInterface
{
    /**
     * @param UpdateInterface $request
     *
     * @return ApiResource
     */
    public function updateApiResource(UpdateInterface $request): ApiResource;

    /**
     * @param UpdateInterface $request
     *
     * @return bool
     */
    public function supportAlso(UpdateInterface $request): bool;
}
