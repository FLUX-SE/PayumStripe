<?php

declare(strict_types=1);

namespace Prometee\PayumStripeCheckoutSession\Action\Api\Resource;

use Stripe\ApiOperations\Retrieve;
use Stripe\ApiResource;

/**
 * @method string|Retrieve getApiResourceClass()
 */
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
