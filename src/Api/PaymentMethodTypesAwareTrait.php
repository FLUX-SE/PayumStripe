<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Api;

trait PaymentMethodTypesAwareTrait
{
    /** @var string[] */
    private $paymentMethodTypes;

    /**
     * @param string[] $paymentMethodTypes
     */
    public function __construct(array $paymentMethodTypes)
    {
        $this->paymentMethodTypes = $paymentMethodTypes;
    }

    public function getPaymentMethodTypes(): array
    {
        return $this->paymentMethodTypes;
    }

    public function setPaymentMethodTypes(array $paymentMethodTypes): void
    {
        $this->paymentMethodTypes = $paymentMethodTypes;
    }

    public function hasPaymentMethodType(string $paymentMethodType): bool
    {
        return in_array($paymentMethodType, $this->paymentMethodTypes);
    }

    public function addPaymentMethodType(string $paymentMethodType): void
    {
        if (!$this->hasPaymentMethodType($paymentMethodType)) {
            $this->paymentMethodTypes[] = $paymentMethodType;
        }
    }
}
