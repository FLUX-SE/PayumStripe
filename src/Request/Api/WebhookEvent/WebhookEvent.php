<?php

declare(strict_types=1);

namespace Prometee\PayumStripeCheckoutSession\Request\Api\WebhookEvent;

use Payum\Core\Request\Generic;
use Prometee\PayumStripeCheckoutSession\Wrapper\EventWrapper;

/**
 * @method null|EventWrapper getModel()
 * @method void setModel(EventWrapper $model)()
 */
final class WebhookEvent extends Generic
{
    public function __construct(EventWrapper $model)
    {
        parent::__construct($model);
    }
}
