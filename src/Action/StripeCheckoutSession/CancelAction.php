<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\StripeCheckoutSession;

use ArrayAccess;
use FluxSE\PayumStripe\Request\Api\Resource\ExpireSession;
use FluxSE\PayumStripe\Request\Api\Resource\RetrieveSession;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Cancel;
use Stripe\Checkout\Session;

class CancelAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * @param Cancel $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if ($model['status'] !== Session::STATUS_OPEN) {
            return;
        }

        /** @var string $id */
        $id = $model['id'];
        $this->gateway->execute(new ExpireSession($id));
    }

    public function supports($request): bool
    {
        if (!$request instanceof Cancel) {
            return false;
        }

        $model = $request->getModel();
        if (!$model instanceof ArrayAccess) {
            return false;
        }

        return $model->offsetExists('object') && $model->offsetGet('object') === Session::OBJECT_NAME;
    }
}
