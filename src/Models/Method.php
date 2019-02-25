<?php

namespace Mollie\Models;

/**
 * Class Method
 * @package Mollie\Models
 */
class Method implements \JsonSerializable
{
    /**
     * @var string
     */
    public $id = '';

    /**
     * @var MethodSetting
     */
    public $settings = null;

    /**
     * @var string
     */
    public $description = '';

    /**
     * @var array
     */
    public $images = [];

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        return [
            'id'          => $this->id,
            'settings'    => $this->settings,
            'description' => $this->description,
            'images'      => $this->images,
        ];
    }
}