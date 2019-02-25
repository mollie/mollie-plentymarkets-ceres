<?php

namespace Mollie\Services;

use Mollie\Api\ApiClient;
use Mollie\Factories\ApiOrderFactory;
use Plenty\Modules\Order\Models\Order;
use Plenty\Modules\Order\Property\Models\OrderProperty;
use Plenty\Modules\Order\Property\Models\OrderPropertyType;

/**
 * Class ShipmentService
 * @package Mollie\Services
 */
class ShipmentService
{
    /**
     * @var ApiOrderFactory
     */
    private $apiOrderFactory;

    /**
     * @var ApiClient
     */
    private $apiClient;

    /**
     * ShipmentService constructor.
     * @param ApiOrderFactory $apiOrderFactory
     * @param ApiClient $apiClient
     */
    public function __construct(ApiOrderFactory $apiOrderFactory, ApiClient $apiClient)
    {
        $this->apiOrderFactory = $apiOrderFactory;
        $this->apiClient       = $apiClient;
    }

    /**
     * @param Order $order
     */
    public function createShipment(Order $order)
    {
        $externalOrderId = '';
        foreach ($order->properties as $orderProperty) {
            if ($orderProperty instanceof OrderProperty) {
                if ($orderProperty->typeId == OrderPropertyType::EXTERNAL_ORDER_ID && !empty($orderProperty->value)) {
                    $externalOrderId = $orderProperty->value;
                }
            }
        }

        if (!empty($externalOrderId)) {
            $shipmentData = $this->apiOrderFactory->buildShipmentData($order);
            $this->apiClient->createShipment($externalOrderId, $shipmentData);
        }
    }
}