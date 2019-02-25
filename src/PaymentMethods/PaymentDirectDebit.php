<?php

namespace Mollie\PaymentMethods;

/**
 * Class PaymentDirectDebit
 * @package Mollie\PaymentMethods
 */
class PaymentDirectDebit extends GenericMolliePayment
{
    /**
     * @return string
     */
    protected function getId(): string
    {
        return 'directdebit';
    }
}