<?php

use Mollie\Api\MollieApiClient;

try {
    $apiClient = new MollieApiClient();
    $apiClient->addVersionString('Plentymarkets/' . SdkRestApi::getParam('pluginVersion'));
    $apiClient->setApiKey(SdkRestApi::getParam('apiKey'));

    $payment = $apiClient->payments->get(SdkRestApi::getParam('paymentId'));
    if ($payment instanceof \Mollie\Api\Resources\Payment) {

        $body = json_encode(
            [
                'description' => 'OrderID ' . SdkRestApi::getParam('newOrderNumber'),
                'metadata'    => [
                    'orderId' => SdkRestApi::getParam('newOrderNumber')
                ]
            ]
        );

        $result = $apiClient->performHttpCallToFullUrl(MollieApiClient::HTTP_PATCH, $payment->_links->self->href, $body);

        return \Mollie\Api\Resources\ResourceFactory::createFromApiResult($result, new \Mollie\Api\Resources\Payment($apiClient));
    }
    return ['error' => 'patch payment failed'];

} catch (\Exception $e) {
    return ['error' => $e->getMessage()];
}


