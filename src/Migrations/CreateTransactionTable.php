<?php

namespace Mollie\Migrations;

use Mollie\Models\MethodSetting;
use Mollie\Models\Transaction;
use Plenty\Modules\Plugin\DataBase\Contracts\Migrate;

/**
 * Class CreateTransactionTable
 * @package Mollie\Migrations
 */
class CreateTransactionTable
{
    /**
     * @var Migrate
     */
    private $migrate;

    /**
     * CreateTransactionTable constructor.
     * @param Migrate $migrate
     */
    public function __construct(Migrate $migrate)
    {
        $this->migrate = $migrate;
    }

    /**
     *
     */
    public function run()
    {
        $this->migrate->createTable(Transaction::class);
    }
}