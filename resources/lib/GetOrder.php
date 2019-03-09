<?php

use Mollie\Api\MollieApiClient;

try {
    $apiClient = new MollieApiClient();
    $apiClient->addVersionString('Plentymarkets/' . SdkRestApi::getParam('pluginVersion'));

    $apiClient->setApiKey(SdkRestApi::getParam('apiKey'));

    return $apiClient->orders->get(SdkRestApi::getParam('orderId'));

} catch (\Exception $e) {
    return ['error' => $e->getMessage()];
}
