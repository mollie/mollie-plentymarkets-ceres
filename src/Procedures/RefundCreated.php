<?php

namespace Mollie\Procedures;

use Mollie\Services\OrderService;
use Mollie\Traits\CanCheckMollieMethod;
use Plenty\Modules\EventProcedures\Events\EventProceduresTriggered;
use Plenty\Modules\Order\Models\Order;
use Plenty\Modules\Order\Models\OrderType;
use Plenty\Modules\Payment\Method\Models\PaymentMethod;

/**
 * Class RefundCreated
 * @package Mollie\Procedures
 */
class RefundCreated
{
    use CanCheckMollieMethod;

    /**
     * @param EventProceduresTriggered $eventTriggered
     */
    public function run(EventProceduresTriggered $eventTriggered)
    {
        if ($eventTriggered->getOrder() instanceof Order && $eventTriggered->getOrder()->typeId == OrderType::TYPE_CREDIT_NOTE) {

            //check if order is using a mollie payment service
            $paymentMethod = $this->getMolliePaymentMethod($eventTriggered->getOrder()->methodOfPaymentId);
            if ($paymentMethod instanceof PaymentMethod) {

                //create a refund

                /** @var OrderService $orderService
                 */
                $orderService = pluginApp(OrderService::class);
                $orderService->createRefund($eventTriggered->getOrder());
            }
        }
    }
}