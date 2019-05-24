<?php

namespace Mollie\Factories;

use Mollie\Factories\Providers\CheckoutProvider;
use Mollie\Factories\Providers\OrderProvider;
use Mollie\Helpers\TrackingURLHelper;
use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use Plenty\Modules\Order\Models\Order;
use Plenty\Modules\Order\Shipping\Contracts\ParcelServicePresetRepositoryContract;
use Plenty\Modules\Order\Shipping\Package\Contracts\OrderShippingPackageRepositoryContract;
use Plenty\Modules\Order\Shipping\Package\Models\OrderShippingPackage;

/**
 * Class ApiOrderFactory
 * @package Mollie\Factories
 */
class ApiOrderFactory
{
    /**
     * @param string $method
     * @param array $options
     * @return array
     */
    public function buildOrder($method, $options = [])
    {
        if (array_key_exists('orderId', $options)) {
            /** @var OrderRepositoryContract $orderRepo */
            $orderRepo = pluginApp(OrderRepositoryContract::class);

            $options['order'] = $orderRepo->findOrderById((INT)$options['orderId']);
        }

        if (array_key_exists('order', $options) && $options['order'] instanceof Order) {
            /** @var OrderProvider $orderProvider */
            $orderProvider = pluginApp(OrderProvider::class);
            return $orderProvider->buildOrder($method, $options);
        }

        /** @var CheckoutProvider $checkoutProvider */
        $checkoutProvider = pluginApp(CheckoutProvider::class);
        return $checkoutProvider->buildOrder($method, $options);
    }

    /**
     * @param Order $order
     * @return array
     */
    public function buildShipmentData(Order $order)
    {
        $shipment = [
            'lines' => []
        ];

        $trackingNumber = '';

        /** @var OrderShippingPackageRepositoryContract $orderShippingPackageRepository */
        $orderShippingPackageRepository = pluginApp(OrderShippingPackageRepositoryContract::class);

        foreach ($orderShippingPackageRepository->listOrderShippingPackages($order->id) as $orderShippingPackage) {
            if ($orderShippingPackage instanceof OrderShippingPackage) {
                $trackingNumber = $orderShippingPackage->packageNumber;
                break;
            }
        }

        if (!empty($trackingNumber)) {
            /** @var ParcelServicePresetRepositoryContract $parcelServicePresetRepository */
            $parcelServicePresetRepository = pluginApp(ParcelServicePresetRepositoryContract::class);

            $parcelServicePreset = $parcelServicePresetRepository->getPresetById($order->shippingProfileId);

            $shipment['tracking'] = [
                'code'    => $trackingNumber,
                'carrier' => $parcelServicePreset->parcelService->backendName
            ];

            if (!empty($parcelServicePreset->parcelService->trackingUrl)) {
                /** @var TrackingURLHelper $trackingURLHelper */
                $trackingURLHelper = pluginApp(TrackingURLHelper::class);

                $shipment['tracking']['url'] = $trackingURLHelper->generateURL(
                    $parcelServicePreset->parcelService->trackingUrl,
                    $trackingNumber,
                    $order->deliveryAddress->postalCode
                );
            }

            return $shipment;
        }

        return $shipment;
    }
}