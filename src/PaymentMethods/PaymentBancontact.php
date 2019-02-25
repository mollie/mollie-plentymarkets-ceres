<?php

namespace Mollie\PaymentMethods;

/**
 * Class PaymentBancontact
 * @package Mollie\PaymentMethods
 */
class PaymentBancontact extends GenericMolliePayment
{
    /**
     * @return string
     */
    protected function getId(): string
    {
        return 'bancontact';
    }
}