<?php

namespace Mollie\PaymentMethods;

/**
 * Class PaymentPaypal
 * @package Mollie\PaymentMethods
 */
class PaymentPaypal extends GenericMolliePayment
{
    /**
     * @return string
     */
    protected function getId(): string
    {
        return 'paypal';
    }
}