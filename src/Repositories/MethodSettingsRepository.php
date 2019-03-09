<?php

namespace Mollie\Repositories;

use Mollie\Contracts\MethodSettingsRepositoryContract;
use Mollie\Models\MethodSetting;
use Mollie\Validators\SaveMethodSettingValidator;
use Plenty\Modules\Payment\Method\Contracts\PaymentMethodRepositoryContract;
use Plenty\Modules\Plugin\DataBase\Contracts\DataBase;
use Plenty\Modules\Plugin\DataBase\Contracts\Model;

/**
 * Class MethodRepository
 * @package Mollie\Repositories
 */
class MethodSettingsRepository implements MethodSettingsRepositoryContract
{
    /**
     * @var DataBase
     */
    private $dataBase;

    /**
     * @var PaymentMethodRepositoryContract
     */
    private $paymentMethodRepository;

    /**
     * @inheritdoc
     */
    public function __construct(DataBase $dataBase, PaymentMethodRepositoryContract $paymentMethodRepository)
    {
        $this->dataBase                = $dataBase;
        $this->paymentMethodRepository = $paymentMethodRepository;
    }

    /**
     * @inheritdoc
     */
    public function getMethodSettingsMap()
    {
        return $this->buildMap(
            $this->dataBase->query(MethodSetting::class)->get()
        );
    }

    /**
     * @inheritdoc
     */
    public function getActiveMethodSettingsMap()
    {
        return $this->buildMap(
            $this->dataBase->query(MethodSetting::class)
                ->where('isActive', '=', true)
                ->get()
        );
    }

    /**
     * @inheritdoc
     */
    public function saveMethodSettings($methodSettingsData)
    {
        SaveMethodSettingValidator::validateOrFail($methodSettingsData);

        $methodSettings = $this->dataBase->find(MethodSetting::class, $methodSettingsData['id']);
        if (!$methodSettings instanceof MethodSetting) {
            /** @var MethodSetting $methodSettings */
            $methodSettings     = pluginApp(MethodSetting::class);
            $methodSettings->id = $methodSettingsData['id'];
        }

        $methodSettings->isActive = $methodSettingsData['isActive'];
        $methodSettings->names    = $methodSettingsData['names'];
        $methodSettings->position = $methodSettingsData['position'];

        $this->persistPlentymarketsPaymentMethod($methodSettingsData['id'], $methodSettingsData['description']);
        $methodSettings = $this->dataBase->save($methodSettings);
        if ($methodSettings instanceof MethodSetting) {
            $this->castSettings($methodSettings);
        }
        return $methodSettings;

    }

    /**
     * @param MethodSetting|Model $methodSetting
     * @return MethodSetting
     */
    private function castSettings(MethodSetting $methodSetting)
    {
        $methodSetting->isActive = ($methodSetting->isActive === true || $methodSetting->isActive == 1);
        $methodSetting->position = (int)$methodSetting->position;
        return $methodSetting;
    }

    /**
     * @param MethodSetting[] $methodSettingsList
     * @return array
     */
    private function buildMap($methodSettingsList)
    {
        $methodSettingsMap = [];

        foreach ($methodSettingsList as $methodSettings) {
            $methodSettingsMap[$methodSettings->id] = $this->castSettings($methodSettings);
        }

        return $methodSettingsMap;
    }

    /**
     * @param string $mollieId
     * @param string $mollieDescription
     * @return void
     */
    private function persistPlentymarketsPaymentMethod($mollieId, $mollieDescription)
    {
        $paymentMethods = $this->paymentMethodRepository->allForPlugin('Mollie');

        if (!is_null($paymentMethods)) {
            foreach ($paymentMethods as $paymentMethod) {
                if ($paymentMethod->paymentKey == $mollieId) {
                    return;
                }
            }
        }

        //create new payment method

        $paymentMethodData = array('pluginKey'  => 'Mollie',
                                   'paymentKey' => $mollieId,
                                   'name'       => 'mollie: ' . $mollieDescription);

        $this->paymentMethodRepository->createPaymentMethod($paymentMethodData);
    }
}