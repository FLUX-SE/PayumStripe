<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\AllInterface;
use Stripe\Collection;

interface AllResourceActionInterface extends ResourceActionInterface
{
    public function allApiResource(AllInterface $request): Collection;

    public function supportAlso(AllInterface $request): bool;
}
