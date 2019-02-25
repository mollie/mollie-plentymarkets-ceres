<?php

namespace Mollie\PaymentMethods;

/**
 * Class PaymentBitcoin
 * @package Mollie\PaymentMethods
 */
class PaymentBitcoin extends GenericMolliePayment
{
    /**
     * @return string
     */
    protected function getId(): string
    {
        return 'bitcoin';
    }
}