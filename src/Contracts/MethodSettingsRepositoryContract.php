<?php

namespace Mollie\Contracts;

use Mollie\Models\MethodSetting;
use Mollie\Validators\SaveMethodSettingValidator;
use Plenty\Exceptions\ValidationException;

/**
 * Interface MethodRepositoryContract
 * @package Mollie\Contracts
 */
interface MethodSettingsRepositoryContract
{
    /**
     * Get all stored method settings mapped by their id
     *
     * @return MethodSetting[]
     */
    public function getMethodSettingsMap();

    /**
     * Get stored active method settings mapped by their id
     *
     * @return MethodSetting[]
     */
    public function getActiveMethodSettingsMap();

    /**
     * Save method settings
     *
     * @param array $methodSettingsData
     * @return MethodSetting
     *
     * @see SaveMethodSettingValidator
     * @throws ValidationException
     */
    public function saveMethodSettings($methodSettingsData);
}