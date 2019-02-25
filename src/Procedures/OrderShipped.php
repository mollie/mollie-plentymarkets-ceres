<?php

namespace Mollie\Procedures;

use Mollie\Services\ShipmentService;
use Mollie\Traits\CanCheckMollieMethod;
use Plenty\Modules\EventProcedures\Events\EventProceduresTriggered;
use Plenty\Modules\Order\Models\Order;
use Plenty\Modules\Payment\Method\Models\PaymentMethod;

/**
 * Class OrderShipped
 * @package Mollie\Procedures
 */
class OrderShipped
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

                //create a shipment

                /** @var ShipmentService $shipmentService */
                $shipmentService = pluginApp(ShipmentService::class);
                $shipmentService->createShipment($eventTriggered->getOrder());
            }
        }
    }

}