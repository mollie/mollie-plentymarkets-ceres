<?php

namespace Mollie\Traits;

/**
 * Trait CanHandleTransactionId
 * @package Mollie\Traits
 */
trait CanHandleTransactionId
{
    /**
     * @param string $transactionId
     * @return string
     */
    private function wrapTransactionId($transactionId)
    {
        return 'prepared_' . $transactionId;
    }

    /**
     * @param $transactionId
     * @return bool|string
     */
    private function unwrapTransactionId($transactionId)
    {
        if ($this->isWrapped($transactionId)) {
            return substr($transactionId, 9);
        }
    }

    /**
     * @param string $transactionId
     * @return bool
     */
    private function isWrapped($transactionId)
    {
        return substr($transactionId, 0, 9) == 'prepared_';
    }
}