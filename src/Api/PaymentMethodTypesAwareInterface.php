<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Api;

interface PaymentMethodTypesAwareInterface
{
    /**
     * @return string[]
     */
    public function getPaymentMethodTypes(): array;

    /**
     * @param string[] $paymentMethodTypes
     */
    public function setPaymentMethodTypes(array $paymentMethodTypes): void;

    public function hasPaymentMethodType(string $paymentMethodType): bool;

    public function addPaymentMethodType(string $paymentMethodType): void;
}
