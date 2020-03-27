<?php

declare(strict_types=1);

namespace Prometee\PayumStripeCheckoutSession\Action\Api\Resource;

use Payum\Core\Bridge\Spl\ArrayObject;
use Stripe\ApiOperations\Create;
use Stripe\ApiResource;

/**
 * @method string|Create getApiResourceClass()
 */
interface CreateResourceActionInterface extends ResourceActionInterface
{
    /**
     * @param ArrayObject $model
     * @param array|null $options
     *
     * @return ApiResource
     */
    public function createApiResource(ArrayObject $model, ?array $options = null): ApiResource;

    /**
     * @param $request
     *
     * @return bool
     */
    public function supportAlso($request): bool;
}
