<?php

namespace Mollie\PaymentMethods;

/**
 * Class PaymentIdeal
 * @package Mollie\PaymentMethods
 */
class PaymentIdeal extends GenericMolliePayment
{
    /**
     * @return string
     */
    protected function getId(): string
    {
        return 'ideal';
    }
}