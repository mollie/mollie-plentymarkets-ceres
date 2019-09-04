<?php

use Mollie\Api\MollieApiClient;

try {
    $apiClient = new MollieApiClient();
    $apiClient->addVersionString('Plentymarkets/' . SdkRestApi::getParam('pluginVersion'));

    $apiClient->setApiKey(SdkRestApi::getParam('apiKey'));

    $params = [];
    if(SdkRestApi::getParam('withPayments')){
        $params['embed'] = 'payments';
    }

    return $apiClient->orders->get(SdkRestApi::getParam('orderId'), $params);

} catch (\Exception $e) {
    return ['error' => $e->getMessage()];
}
