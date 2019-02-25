<?php

use Mollie\Api\MollieApiClient;

try {
    $apiClient = new MollieApiClient();
    $apiClient->setApiKey(SdkRestApi::getParam('apiKey'));

    return $apiClient->orders->cancel(SdkRestApi::getParam('orderId'));

} catch (\Exception $e) {
    return ['error' => $e->getMessage()];
}
