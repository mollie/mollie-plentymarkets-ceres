<?php

namespace Mollie\Procedures;

use Mollie\Services\OrderService;
use Mollie\Traits\CanCheckMollieMethod;
use Plenty\Modules\EventProcedures\Events\EventProceduresTriggered;
use Plenty\Modules\Order\Models\Order;
use Plenty\Modules\Payment\Method\Models\PaymentMethod;

/**
 * Class OrderCanceled
 * @package Mollie\Procedures
 */
class OrderCanceled
{
    use CanCheckMollieMethod;

    /**
     * @param EventProceduresTriggered $eventTriggered
     */
    public function run(EventProceduresTriggered $eventTriggered)
    {
        if ($eventTriggered->getOrder() instanceof Order) {

            //check if order is using a mollie payment service
            $paymentMethod = $this->getMolliePaymentMethod($eventTriggered->getOrder()->methodOfPaymentId);
            if ($paymentMethod instanceof PaymentMethod) {

                //cancel an order

                /** @var OrderService $orderService */
                $orderService = pluginApp(OrderService::class);
                $orderService->cancelOrder($eventTriggered->getOrder());
            }
        }
    }
}