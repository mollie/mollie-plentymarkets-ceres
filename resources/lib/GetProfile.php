<?php
use Mollie\Api\MollieApiClient;

try {
    $apiClient = new MollieApiClient();
    $apiClient->addVersionString('Plentymarkets/' . SdkRestApi::getParam('pluginVersion'));

    $apiClient->setApiKey(SdkRestApi::getParam('apiKey'));

    return $apiClient->profiles->getCurrent();
} catch (\Exception $e) {
    return ['error' => $e->getMessage()];
}
