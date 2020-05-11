<?php

declare(strict_types=1);

namespace Prometee\PayumStripe\Action\Api\Resource;

use Payum\Core\Exception\LogicException;
use Prometee\PayumStripe\Request\Api\Resource\CreateInterface;
use Prometee\PayumStripe\Request\Api\Resource\CreatePaymentIntent;
use Stripe\ApiOperations\Create;
use Stripe\ApiResource;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class CreatePaymentIntentAction extends AbstractCreateAction
{
    /** @var string|PaymentIntent */
    protected $apiResourceClass = PaymentIntent::class;

    public function createApiResource(CreateInterface $request): ApiResource
    {
        $apiResourceClass = $this->getApiResourceClass();
        if (false === method_exists($apiResourceClass, 'create')) {
            throw new LogicException(sprintf('This class "%s" is not an instance of "%s"', (string) $apiResourceClass, Create::class));
        }

        Stripe::setApiKey($this->api->getSecretKey());

        $params = $request->getParameters();

        /** @see Create::create() */
        /** @var PaymentIntent $paymentIntent */
        $paymentIntent = $apiResourceClass::create([
            'amount' => $params['amount'],
            'currency' => $params['currency'],
            'payment_method_types' => $params['payment_method_types'],
            'metadata' => isset($params['metadata']) ? $params['metadata'] : [],
            'customer' => isset($params['customer']) && isset($params['customer']['id']) ? $params['customer']['id'] : null,
            'payment_method' => isset($params['payment_method']) && isset($params['payment_method']['id']) ? $params['payment_method']['id'] : null,
            'setup_future_usage' => isset($params['payment_method']) && isset($params['payment_method']['id']) ? 'off_session' : null,
        ], $request->getOptions());

        return $paymentIntent;
    }

    /**
     * {@inheritdoc}
     */
    public function supportAlso(CreateInterface $request): bool
    {
        return $request instanceof CreatePaymentIntent;
    }
}
