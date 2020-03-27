<?php

declare(strict_types=1);

namespace Prometee\PayumStripeCheckoutSession\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\RenderTemplate;
use Prometee\PayumStripeCheckoutSession\Request\Api\RedirectToCheckout;

final class RedirectToCheckoutAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;
    use StripeApiAwareTrait {
        StripeApiAwareTrait::__construct as private __stripeApiAwareTraitConstruct;
    }

    /**
     * @var string
     */
    protected $templateName;

    /**
     * @param string $templateName
     */
    public function __construct(string $templateName)
    {
        $this->templateName = $templateName;
        $this->__stripeApiAwareTraitConstruct();
    }

    /**
     * {@inheritDoc}
     *
     * @param RedirectToCheckout $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $this->gateway->execute($renderTemplate = new RenderTemplate($this->templateName, array(
            'model' => $request->getModel(),
            'publishable_key' => $this->api->getPublishableKey(),
            'actionUrl' => $request->getToken() ? $request->getToken()->getTargetUrl() : null,
        )));

        throw new HttpResponse($renderTemplate->getResult());
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof RedirectToCheckout;
    }
}
