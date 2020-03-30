<?php

declare(strict_types=1);

namespace Prometee\PayumStripeCheckoutSession\Action\Api\Resource;

use Stripe\ApiResource;

interface RetrieveResourceActionInterface extends ResourceActionInterface
{
    /**
     * @param string $id
     * @param null|array $options
     *
     * @return ApiResource
     */
    public function retrieveApiResource(string $id, ?array $options = null): ApiResource;

    /**
     * @param $request
     *
     * @return bool
     */
    public function supportAlso($request): bool;
}
