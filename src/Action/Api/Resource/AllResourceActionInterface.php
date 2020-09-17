<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\AllInterface;
use Stripe\ApiResource;
use Stripe\Collection;

interface AllResourceActionInterface extends ResourceActionInterface
{
    /**
     * @return ApiResource[]|Collection
     */
    public function allApiResource(AllInterface $request): Collection;

    public function supportAlso(AllInterface $request): bool;
}
