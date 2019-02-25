<?php

namespace Mollie\PaymentMethods;

/**
 * Class PaymentKlarnaSliceIt
 * @package Mollie\PaymentMethods
 */
class PaymentKlarnaSliceIt extends GenericMolliePayment
{
    /**
     * @return string
     */
    protected function getId(): string
    {
        return 'klarnasliceit';
    }
}