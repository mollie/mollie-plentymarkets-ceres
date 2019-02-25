<?php

namespace Mollie\PaymentMethods;

/**
 * Class PaymentGiftcard
 * @package Mollie\PaymentMethods
 */
class PaymentGiftcard extends GenericMolliePayment
{
    /**
     * @return string
     */
    protected function getId(): string
    {
        return 'giftcard';
    }
}