<?php

namespace Mollie\Repositories;

use Mollie\Contracts\TransactionRepositoryContract;
use Mollie\Models\Transaction;
use Plenty\Modules\Frontend\Session\Storage\Contracts\FrontendSessionStorageFactoryContract;
use Plenty\Modules\Plugin\DataBase\Contracts\DataBase;

/**
 * Class TransactionRepository
 * @package Mollie\Repositories
 */
class TransactionRepository implements TransactionRepositoryContract
{
    const SESSION_KEY = 'Mollie_TransactionId';

    /**
     * @var DataBase
     */
    private $dataBase;

    /**
     * @var FrontendSessionStorageFactoryContract
     */
    private $frontendSessionStorageFactory;

    /**
     * TransactionRepository constructor.
     * @param DataBase $dataBase
     * @param FrontendSessionStorageFactoryContract $frontendSessionStorageFactory
     */
    public function __construct(DataBase $dataBase,
                                FrontendSessionStorageFactoryContract $frontendSessionStorageFactory)
    {
        $this->dataBase                      = $dataBase;
        $this->frontendSessionStorageFactory = $frontendSessionStorageFactory;
    }

    /**
     * @return Transaction|\Plenty\Modules\Plugin\DataBase\Contracts\Model
     */
    public function createTransaction()
    {
        /** @var Transaction $transaction */
        $transaction            = pluginApp(Transaction::class);
        $transaction->timestamp = time();

        $transaction = $this->dataBase->save($transaction);
        if ($transaction instanceof Transaction) {
            $this->frontendSessionStorageFactory->getPlugin()->setValue(self::SESSION_KEY, $transaction->transactionId);
        }

        return $transaction;
    }

    /**
     * @param int $orderId
     */
    public function assignOrderId($orderId)
    {
        $transaction = $this->getTransaction();
        if ($transaction instanceof Transaction) {
            $transaction->orderId = $orderId;
            $this->dataBase->save($transaction);
            $this->frontendSessionStorageFactory->getPlugin()->setValue(self::SESSION_KEY, '');
        }
    }

    /**
     * @param string $mollieOrderId
     */
    public function assignMollieOrderId($mollieOrderId)
    {
        $transaction = $this->getTransaction();
        if ($transaction instanceof Transaction) {
            $transaction->mollieOrderId = $mollieOrderId;
            $this->dataBase->save($transaction);
        }
    }


    /**
     * @param string $transactionId
     */
    public function setTransactionPaid($transactionId)
    {
        $transaction = $this->getTransaction();
        if ($transaction instanceof Transaction) {
            $transaction->isPaid = true;
            $this->dataBase->save($transaction);
        }
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function isTransactionPaid()
    {
        $transaction = $this->getTransaction();
        if ($transaction instanceof Transaction) {
            return $transaction->isPaid;
        }
        return false;
    }

    /**
     * @return boolean
     */
    public function openTransactionExists()
    {
        return !empty($this->getTransactionId());
    }

    /**
     * @return string
     */
    public function getTransactionId()
    {
        return $this->frontendSessionStorageFactory->getPlugin()->getValue(self::SESSION_KEY);
    }

    /**
     * @return Transaction|\Plenty\Modules\Plugin\DataBase\Contracts\Model
     */
    public function getTransaction()
    {
        return $this->dataBase->find(Transaction::class, $this->getTransactionId());
    }
}