<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\StripeJs;

use ArrayAccess;
use FluxSE\PayumStripe\Request\Api\Resource\CancelPaymentIntent;
use FluxSE\PayumStripe\Request\Api\Resource\CancelSetupIntent;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Cancel;
use Stripe\PaymentIntent;
use Stripe\SetupIntent;

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

        if ($model['object'] === PaymentIntent::OBJECT_NAME) {
            $this->cancelPaymentIntent($model);
        }

        if ($model['object'] === SetupIntent::OBJECT_NAME) {
            $this->cancelSetupIntent($model);
        }
    }

    private function cancelPaymentIntent(ArrayObject $model): void
    {
        if ($model['status'] === PaymentIntent::STATUS_CANCELED) {
            return;
        }
        if ($model['status'] === PaymentIntent::STATUS_SUCCEEDED) {
            return;
        }

        /** @var string $id */
        $id = $model['id'];
        $this->gateway->execute(new CancelPaymentIntent($id));
    }

    private function cancelSetupIntent(ArrayObject $model): void
    {
        if ($model['status'] === SetupIntent::STATUS_CANCELED) {
            return;
        }

        if ($model['status'] === SetupIntent::STATUS_SUCCEEDED) {
            return;
        }

        /** @var string $id */
        $id = $model['id'];
        $this->gateway->execute(new CancelSetupIntent($id));
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

        if (!$model->offsetExists('object')) {
            return false;
        }

        return in_array($model->offsetGet('object'), [ PaymentIntent::OBJECT_NAME, SetupIntent::OBJECT_NAME ], true);
    }
}
