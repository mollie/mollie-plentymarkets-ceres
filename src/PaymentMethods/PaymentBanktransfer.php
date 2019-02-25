<?php

namespace Mollie\PaymentMethods;

/**
 * Class PaymentBanktransfer
 * @package Mollie\PaymentMethods
 */
class PaymentBanktransfer extends GenericMolliePayment
{
    /**
     * @return string
     */
    protected function getId(): string
    {
        return 'banktransfer';
    }
}