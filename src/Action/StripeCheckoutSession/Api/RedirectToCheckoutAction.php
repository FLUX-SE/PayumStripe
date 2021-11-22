<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\StripeCheckoutSession\Api;

use ArrayAccess;
use FluxSE\PayumStripe\Request\StripeCheckoutSession\Api\RedirectToCheckout;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Reply\HttpRedirect;

final class RedirectToCheckoutAction implements ActionInterface
{
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var ArrayAccess $model */
        $model = $request->getModel();
        if ($model->offsetExists('url')) {
            throw new HttpRedirect($model->offsetGet('url'));
        }

        throw RequestNotSupportedException::create($request);
    }

    public function supports($request): bool
    {
        return $request instanceof RedirectToCheckout
            && $request->getModel() instanceof ArrayAccess;
    }
}
