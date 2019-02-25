<?php

namespace Mollie\PaymentMethods;

/**
 * Class PaymentEPS
 * @package Mollie\PaymentMethods
 */
class PaymentEPS extends GenericMolliePayment
{
    /**
     * @return string
     */
    protected function getId(): string
    {
        return 'eps';
    }
}