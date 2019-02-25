<?php

namespace Mollie\PaymentMethods;

/**
 * Class PaymentPaySafeCard
 * @package Mollie\PaymentMethods
 */
class PaymentPaySafeCard extends GenericMolliePayment
{
    /**
     * @return string
     */
    protected function getId(): string
    {
        return 'paysafecard';
    }
}