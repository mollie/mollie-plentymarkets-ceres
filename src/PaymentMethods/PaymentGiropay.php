<?php

namespace Mollie\PaymentMethods;

/**
 * Class PaymentGiropay
 * @package Mollie\PaymentMethods
 */
class PaymentGiropay extends GenericMolliePayment
{
    /**
     * @return string
     */
    protected function getId(): string
    {
        return 'giropay';
    }
}