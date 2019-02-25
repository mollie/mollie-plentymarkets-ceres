<?php

namespace Mollie\DataProviders;

use Plenty\Modules\Order\Models\Order;
use Plenty\Modules\Payment\Method\Contracts\PaymentMethodRepositoryContract;
use Plenty\Modules\Payment\Method\Models\PaymentMethod;
use Plenty\Plugin\Templates\Twig;

/**
 * Class ReinitPaymentComponent
 * @package Mollie\DataProviders
 */
class ReInitPaymentComponent
{
    /**
     * @param Twig $twig
     * @param array $arg
     * @return string
     */
    public function call(Twig $twig, $arg)
    {
        /** @var PaymentMethodRepositoryContract $paymentMethodRepository */
        $paymentMethodRepository = pluginApp(PaymentMethodRepositoryContract::class);
        $paymentMethods          = $paymentMethodRepository->allForPlugin('Mollie');
        $paymentIds              = [];
        foreach ($paymentMethods as $paymentMethod) {
            if ($paymentMethod instanceof PaymentMethod) {
                $paymentIds[] = $paymentMethod->id;
            }
        }

        return $twig->render('Mollie::DataProviders.ReInitPaymentComponent', ['order' => $arg[0], 'paymentIds' => $paymentIds]);
    }
}