<?php

namespace Mollie\Models;

/**
 * This class is used to avoid max cache size check in plentymarkets...
 *
 * Class MethodsCacheAdapter
 * @package Mollie\Models
 */
class MethodsCacheAdapter implements \JsonSerializable
{
    /**
     * @var array
     */
    public $cachedMethods = [];

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return [];
    }
}