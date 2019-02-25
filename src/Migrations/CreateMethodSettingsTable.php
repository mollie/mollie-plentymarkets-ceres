<?php

namespace Mollie\Migrations;

use Mollie\Models\MethodSetting;
use Plenty\Modules\Plugin\DataBase\Contracts\Migrate;

/**
 * Class CreateMethodSettingsTable
 * @package Mollie\Migrations
 */
class CreateMethodSettingsTable
{
    /**
     * @var Migrate
     */
    private $migrate;

    /**
     * CreateMethodSettingsTable constructor.
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
        $this->migrate->createTable(MethodSetting::class);
    }
}