<?php

namespace Mollie\PaymentMethods;

/**
 * Class PaymentBelfius
 * @package Mollie\PaymentMethods
 */
class PaymentBelfius extends GenericMolliePayment
{
    /**
     * @return string
     */
    protected function getId(): string
    {
        return 'belfius';
    }
}