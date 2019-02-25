<?php

namespace Mollie\PaymentMethods;

/**
 * Class PaymentKlarnaPaylater
 * @package Mollie\PaymentMethods
 */
class PaymentKlarnaPaylater extends GenericMolliePayment
{
    /**
     * @return string
     */
    protected function getId(): string
    {
        return 'klarnapaylater';
    }
}