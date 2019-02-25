<?php

namespace Mollie\Models;

use Plenty\Modules\Plugin\DataBase\Contracts\Model;

/**
 * Class MethodSetting
 * @package Mollie\Models
 *
 * @property string $id
 * @property boolean $isActive
 * @property array $names
 * @property int $position
 */
class MethodSetting extends Model implements \JsonSerializable
{
    /**
     * @var string
     */
    public $id = '';

    /**
     * @var bool
     */
    public $isActive = false;

    /**
     * @var array
     */
    public $names = [];

    /**
     * @var int
     */
    public $position = 0;

    /**
     * @inheritdoc
     */
    protected $primaryKeyFieldType = self::FIELD_TYPE_STRING;

    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'Mollie::MethodSettings';
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        return [
            'id'       => $this->id,
            'isActive' => $this->isActive,
            'names'    => $this->names,
            'position' => $this->position,
        ];
    }
}