<?php

namespace Mollie\PaymentMethods;

use Plenty\Modules\Frontend\Session\Storage\Contracts\FrontendSessionStorageFactoryContract;

/**
 * Class PaymentApplePay
 * @package Mollie\PaymentMethods
 */
class PaymentApplePay extends GenericMolliePayment
{
    /**
     * @return string
     */
    protected function getId(): string
    {
        return 'applepay';
    }

    /**
     * Check whether PayPal Express is active
     *
     * @return bool
     */
    public function isActive(): bool
    {
        $isActive = parent::isActive();
        if($isActive){
            /** @var FrontendSessionStorageFactoryContract $frontendSessionStorageFactory */
            $frontendSessionStorageFactory = pluginApp(FrontendSessionStorageFactoryContract::class);
            return $frontendSessionStorageFactory->getPlugin()->getValue('mollie_apple_pay_active') === true;

        }
        return false;
    }
}