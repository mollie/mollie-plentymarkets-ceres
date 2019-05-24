<?php

namespace Mollie\Contracts;

use Mollie\Models\Transaction;

/**
 * Class TransactionRepositoryContract
 * @package Mollie\Contracts
 */
interface TransactionRepositoryContract
{
    /**
     * @return Transaction
     */
    public function createTransaction();

    /**
     * @param int $orderId
     */
    public function assignOrderId($orderId);

    /**
     * @param string $mollieOrderId
     */
    public function assignMollieOrderId($mollieOrderId);

    /**
     * @return boolean
     */
    public function openTransactionExists();

    /**
     * @return string
     */
    public function getTransactionId();

    /**
     * @param string $transactionId
     */
    public function setTransactionPaid($transactionId);

    /**
     * @return bool
     * @throws \Exception
     */
    public function isTransactionPaid();

    /**
     * @return Transaction
     */
    public function getTransaction();
}