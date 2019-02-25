<?php

namespace Mollie\Traits;

use Plenty\Modules\Payment\Method\Contracts\PaymentMethodRepositoryContract;
use Plenty\Modules\Payment\Method\Models\PaymentMethod;

/**
 * Trait CanCheckMollieMethod
 * @package Mollie\Traits
 */
trait CanCheckMollieMethod
{
    /**
     * @param int $paymentMethodId
     * @return PaymentMethod
     */
    private function getMolliePaymentMethod($paymentMethodId)
    {
        /** @var PaymentMethodRepositoryContract $paymentMethodRepository */
        $paymentMethodRepository = pluginApp(PaymentMethodRepositoryContract::class);
        $paymentMethods          = $paymentMethodRepository->allForPlugin('Mollie');
        foreach ($paymentMethods as $paymentMethod) {
            if ($paymentMethod instanceof PaymentMethod) {
                if ($paymentMethod->id == $paymentMethodId) {
                    return $paymentMethod;
                }
            }
        }
        return null;
    }
}