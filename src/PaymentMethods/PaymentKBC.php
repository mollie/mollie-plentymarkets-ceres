<?php

namespace Mollie\PaymentMethods;

/**
 * Class PaymentKBC
 * @package Mollie\PaymentMethods
 */
class PaymentKBC extends GenericMolliePayment
{
    /**
     * @return string
     */
    protected function getId(): string
    {
        return 'kbc';
    }
}