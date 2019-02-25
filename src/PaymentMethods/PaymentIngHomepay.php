<?php

namespace Mollie\PaymentMethods;

/**
 * Class PaymentIngHomepay
 * @package Mollie\PaymentMethods
 */
class PaymentIngHomepay extends GenericMolliePayment
{
    /**
     * @return string
     */
    protected function getId(): string
    {
        return 'inghomepay';
    }
}