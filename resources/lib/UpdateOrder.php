<?php

use Mollie\Api\MollieApiClient;
try {
    $apiClient = new MollieApiClient();
    $apiClient->addVersionString('Plentymarkets/' . SdkRestApi::getParam('pluginVersion'));
    $apiClient->setApiKey(SdkRestApi::getParam('apiKey'));

    $order = $apiClient->orders->get(SdkRestApi::getParam('orderId'));
    if($order instanceof \Mollie\Api\Resources\Order){

        $body = json_encode(array(
            "orderNumber" => SdkRestApi::getParam('newOrderNumber')
        ));

        $result = $apiClient->performHttpCallToFullUrl(MollieApiClient::HTTP_PATCH, $order->_links->self->href, $body);

        return \Mollie\Api\Resources\ResourceFactory::createFromApiResult($result, new \Mollie\Api\Resources\Order($apiClient));
    }
    return ['error' => 'patch order failed'];

} catch (\Exception $e) {
    return ['error' => $e->getMessage()];
}


