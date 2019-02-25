<?php

use Mollie\Api\MollieApiClient;

try {
    $apiClient = new MollieApiClient();
    $apiClient->setApiKey(SdkRestApi::getParam('apiKey'));

    $order     = new \Mollie\Api\Resources\Order($apiClient);
    $order->id = SdkRestApi::getParam('orderId');

    return $apiClient->shipments->createFor($order, SdkRestApi::getParam('shipmentData'));

} catch (\Exception $e) {
    return ['error' => $e->getMessage()];
}
