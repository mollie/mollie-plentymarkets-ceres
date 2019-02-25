<?php

namespace Mollie\PaymentMethods;

/**
 * Class PaymentSofort
 * @package Mollie\PaymentMethods
 */
class PaymentSofort extends GenericMolliePayment
{
    /**
     * @return string
     */
    protected function getId(): string
    {
        return 'sofort';
    }
}