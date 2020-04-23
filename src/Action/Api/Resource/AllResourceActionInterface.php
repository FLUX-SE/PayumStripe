<?php

declare(strict_types=1);

namespace Prometee\PayumStripe\Action\Api\Resource;

use Prometee\PayumStripe\Request\Api\Resource\AllInterface;
use Stripe\ApiResource;
use Stripe\Collection;

interface AllResourceActionInterface extends ResourceActionInterface
{
    /**
     * @param AllInterface $request
     *
     * @return ApiResource[]|Collection
     */
    public function allApiResource(AllInterface $request): Collection;

    /**
     * @param AllInterface $request
     *
     * @return bool
     */
    public function supportAlso(AllInterface $request): bool;
}
