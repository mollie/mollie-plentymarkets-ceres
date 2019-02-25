<?php

namespace Mollie\PaymentMethods;

/**
 * Class PaymentCreditCard
 * @package Mollie\PaymentMethods
 */
class PaymentCreditCard extends GenericMolliePayment
{
    /**
     * @return string
     */
    protected function getId(): string
    {
        return 'creditcard';
    }
}